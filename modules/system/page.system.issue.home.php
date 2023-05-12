<?php
/**
* Sysgtem :: Issue Home Page
* Created :: 2022-10-14
* Modify  :: 2022-10-20
* Version :: 2
*
* @return Widget
*
* @usage system/issue
*/

class SystemIssueHome extends Page {
	var $right;

 	function __construct() {
		parent::__construct([
			'right' => (Object) [
				'access' => is_admin(),
			],
		]);
	}
	function build() {
		if (!$this->right->access) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_FORBIDDEN,
				'text' => 'Access Denied',
			]);
		}

		$dbs = mydb::select(
			'SELECT *
			FROM %system_issue%
			WHERE `status` != :complete
			ORDER BY `issueId` DESC',
			[':complete' => _COMPLETE]
		);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $dbs->_num_rows.' Issues Report',
				'trailing' => new Row([
					'children' => [
						'<a class="sg-action btn" href="'.url('api/system/issue.close/*').'" data-rel="notify" data-done="reload" data-title="ล้างรายการ" data-confirm="ต้องการเปลี่ยนสถานะทุกรายการให้เป็นเรียบร้อย กรุณายืนยัน?"><i class="icon -material">done_all</i></a>',
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
											'title' => $item->host.$item->path.($item->query ? '?'.$item->query : ''),
											'leading' => $item->issueId,
											'subtitle' => ($item->reportBy ? 'By : '.$item->reportBy : NULL)
												. (' @'.$item->reportDate),
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
														'href' => $item->host.$item->path.($item->query ? '?'.$item->query : ''),
														'icon' => new Icon('public'),
														'attribute' => ['target' => '_blank']
													]), // Button
												], // children
											]), // Nav
										]), // ListTile
										new ScrollView([
											'child' => new Column([
												'class' => '-sg-paddingnorm',
												'children' => [
													'referer : <a href="'.$item->referer.'" target="_blank">'.$item->referer.'</a>',
													'agent : '.$item->agent,
												]
											]), // Column
										]), // ScrollView
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
}
?>