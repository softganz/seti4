<?php
/**
* System  :: Issue Home Page
* Created :: 2022-10-14
* Modify  :: 2025-02-25
* Version :: 14
*
* @return Widget
*
* @usage system/issue
*/

use Softganz\DB;

class SystemIssueHome extends Page {
	var $issueType;
	var $host;
	var $items = 1000;
	var $right;

 	function __construct() {
		parent::__construct([
			'issueType' => post('type'),
			'host' => post('host'),
			'items' => SG\getFirstInt(post('items'), $this->items),
			'right' => (Object) [
				'access' => is_admin(),
			],
		]);
	}

	function rightToBuild() {
		if (!$this->right->access) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		return true;
	}

	function build() {
		head('googlead','<script></script>');

		$dbs = $this->getData();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $dbs->count.' Issues Report',
				'trailing' => $this->trailing(),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					empty($this->host) ? $this->domainCount() : NULL,
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
														'href' => url('api/system/issue/close/'.$item->issueId),
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

	function domainCount() {
		return new Table([
			'thead' => ['Domain', 'error-count -amt' => 'Error Count'],
			'children' => array_map(
				function($domain) {
					return [
						new Button([
							'href' => url('system/issue', ['type' => $this->issueType, 'host' => $domain->host]),
							'text' => $domain->host,
						]),
						number_format($domain->amt)
					];
				},
				(Array) DB::select([
					'SELECT `issue`.`host`, COUNT(*) `amt`
					FROM %system_issue% `issue`
					%WHERE%
					GROUP BY `issue`.`host`',
					'where' => [
						'%WHERE%' => [
							['`issue`.`status` != :status', ':status' => _COMPLETE],
							$this->issueType ? ['`issue`.`issueType` = :issueType', ':issueType' => $this->issueType] : NULL,
						]
					]
				])->items
			),
		]);
	}

	private function getData() {
		return DB::select([
			'SELECT *
			FROM %system_issue% `issue`
			%WHERE%
			ORDER BY `issueId` DESC
			LIMIT $ITEMS$',
			'where' => [
				'%WHERE%' => [
					['`issue`.`status` != :complete', ':complete' => _COMPLETE],
					$this->issueType ? ['`issue`.`issueType` = :issueType', ':issueType' => $this->issueType] : NULL,
					$this->host ? ['`issue`.`host` = :host', ':host' => $this->host] : NULL,
				]
			],
			'var' => [
				'$ITEMS$' => $this->items
				]
		]);
	}

	private function trailing() {
		return new Row([
			'crossAxisAlignment' => 'center',
			'children' => [
				new Form([
					'action' => url('system/issue'),
					'method' => 'GET',
					'children' => [
						'type' => [
							'type' => 'select',
							'onChange' => 'submit',
							'value' => $this->issueType,
							'options' => ['' => 'All', 'Fatal Error' => 'Fatal Error', 'Create user' => 'Create user'],
						],
						'host' => $this->host ? ['type' => 'hidden', 'value' => $this->host] : NULL,
					], // children
				]),
				new Button([
					'type' => 'secondary',
					'class' => 'sg-action',
					'href' => url('api/system/issue/close/*', ['type' => $this->issueType, 'host' => $this->host]),
					'rel' => 'notify',
					'done' => 'reload',
					'icon' => new Icon('done_all'),
					'attribute' => [
						'data-title' => 'ล้างรายการ',
						'data-confirm' => 'ต้องการเปลี่ยนสถานะทุกรายการให้เป็นเรียบร้อย กรุณายืนยัน?',
					]
				]), // Button
				new Button([
					'type' => 'secondary',
					// 'class' => 'sg-action',
					'href' => url('system/issue', ['type' => $this->issueType, 'host' => $this->host]),
					// 'rel' => '#main',
					'icon'=> new Icon('refresh'),
				]),  // Button
			], // children
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