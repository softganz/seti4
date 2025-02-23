<?php
/**
* System  :: Issue Home Page
* Created :: 2022-10-14
* Modify  :: 2025-02-24
* Version :: 11
*
* @return Widget
*
* @usage system/issue
*/

use Softganz\DB;

class SystemIssueHome extends Page {
	var $issueType;
	var $items = 1000;
	var $right;

 	function __construct() {
		parent::__construct([
			'issueType' => post('type'),
			'items' => SG\getFirstInt(post('items'), $this->items),
			'right' => (Object) [
				'access' => is_admin(),
			],
		]);
	}
	function build() {
		head('googlead','<script></script>');

		if (!$this->right->access) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_FORBIDDEN,
				'text' => 'Access Denied',
			]);
		}

		$dbs = DB::select([
			'SELECT *
			FROM %system_issue% `issue`
			%WHERE%
			ORDER BY `issueId` DESC
			LIMIT $ITEMS$',
			'where' => [
				'%WHERE%' => [
					['`issue`.`status` != :complete', ':complete' => _COMPLETE],
					$this->issueType ? ['`issue`.`issueType` = :issueType', ':issueType' => $this->issueType] : NULL,
				]
			],
			'var' => [
				'$ITEMS$' => $this->items
				]
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $dbs->count.' Issues Report',
				'trailing' => new Row([
					'crossAxisAlignment' => 'center',
					'children' => [
						new Form([
							'action' => url('system/issue'),
							'children' => [
								'type' => [
									'type' => 'select',
									'onChange' => 'submit',
									'value' => $this->issueType,
									'options' => ['' => 'All', 'Fatal Error' => 'Fatal Error', 'Create user' => 'Create user'],
								]
							], // children
						]),
						'<a class="sg-action btn" href="'.url('api/system/issue.close/*', ['type' => $this->issueType]).'" data-rel="notify" data-done="reload" data-title="ล้างรายการ" data-confirm="ต้องการเปลี่ยนสถานะทุกรายการให้เป็นเรียบร้อย กรุณายืนยัน?"><i class="icon -material">done_all</i></a>',
						'<a class="sg-action btn" href="'.url('system/issue').'" data-rel="#main"><i class="icon -material">refresh</i></a>'
					], // children
				]), // Row
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Widget([
						// 'thead' => ['ID', 'Host', 'Url', 'Report By', 'report-date -date' => 'Date', '', ''],
						'children' => array_map(
							function($item) {
								return new Card([
									'children' => [
										new ListTile([
											'crossAxisAlignment' => 'center',
											'title' => $item->host,
											'leading' => new Icon($this->issueIcon($item->issueType)),
											// 'subtitle' => ($item->reportBy ? 'By : '.$item->reportBy : NULL)
												// . (' @'.$item->reportDate),
											'trailing' => new Nav([
												'children' => [
													new Button([
														'type' => 'link',
														'href' => url('api/system/issue.close/'.$item->issueId),
														'icon' => new Icon('done'),
														'class' => 'sg-action',
														'rel' => 'none',
														'done' => 'remove:parent .widget-card',
														'attribute' => ['data-title' => 'Close Issue', 'data-confirm' => 'ได้ดำเนินการแก้ไขปัญหานี้เรียบร้อยแล้ว กรุณายืนยัน?',]
													]), // Button
													new Button([
														'type' => 'link',
														'href' => url('system/issue/'.$item->issueId),
														'icon' => new Icon('find_in_page'),
														'class' => 'sg-action',
														'rel' => 'box',
														'attribute' => ['data-width' => 'full']
													]), // Button
													new Button([
														'type' => 'link',
														'href' => $this->createTargetUrl($item),
														'icon' => new Icon('public'),
														'attribute' => ['target' => '_blank']
													]), // Button
												], // children
											]), // Nav
										]), // ListTile
										new ScrollView([
											'child' => new Column([
												'class' => '-sg-paddingnorm -nowrap',
												'children' => [
													'<b>Link : '.$item->host.$item->path.($item->query ? '?'.$item->query : '').'</b>',
													$item->reportBy ? 'By : '.$item->reportBy : NULL,
													$item->issueType === 'Create user' ? $this->showCreateUserInfo($item) : NULL,
													'Date : '.$item->reportDate,
													'Referer : <a href="'.$item->referer.'" target="_blank">'.$item->referer.'</a>',
													'Agent : '.$item->agent,
													in_array($item->issueType, ['Fatal Error']) ? '<details><summary>'.preg_replace('/(<\/li>.*)/', '', $item->description).'</li></ul></summary>Message : '.$item->description.'</details>' : NULL,
												]
											]), // Column
										]), // ScrollView
										// new DebugMsg($item, '$item'),
									], // children
								]);
								// 	$item->issueId,
								// 	$item->host,
								// 	$item->path,
								// 	$item->reportBy,
								// 	$item->reportDate,
								// ];
							},
							$dbs->items
						)
					]), // Table
				], // children
			]), // Widget
		]);
	}

	private function issueIcon($issueType) {
		$icons = [
			'Fatal Error' => 'error',
			'Create user' => 'person_add',
			'Other' => 'priority_high'
		];

		return array_key_exists($issueType, $icons) ? $icons[$issueType] : $icons['Other'];
	}

	private function showCreateUserInfo($item) {
		return $item->description;
	}

	private function createTargetUrl($item) {
		$url = '';
		switch ($item->issueType) {
			case 'Create user':
				$userInfo = json_decode($item->description);
				$url = $item->host.'/profile/'.$userInfo->id;
				break;

			default:
				$url = $item->host.$item->path.($item->query ? '?'.$item->query : '');
				break;
		}
		return $url;
	}
}
?>