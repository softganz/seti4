<?php
/**
* Module  :: Description
* Created :: 2022-10-14
* Modify  :: 2022-10-14
* Version :: 1
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class SystemIssueList extends Page {
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
				'title' => 'Issue Report',
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
											'title' => $item->host.$item->path,
											'leading' => $item->issueId,
											'subtitle' => ($item->reportBy ? 'By : '.$item->reportBy : NULL)
												. (' @'.$item->reportDate),
											'trailing' => new Nav([
												'children' => [
													new Button([
														'href' => url('api/system/issue.close/'.$item->issueId),
														'icon' => new Icon('done'),
														'class' => 'sg-action',
														'rel' => 'none',
														'done' => 'remove:parent .widget-card',
														'attribute' => ['data-title' => 'Close Issue', 'data-confirm' => 'ได้ดำเนินการแก้ไขปัญหานี้เรียบร้อยแล้ว กรุณายืนยัน?',]
													]), // Button
													new Button([
														'href' => $item->host.$item->path,
														'icon' => new Icon('find_in_page'),
														'attribute' => ['target' => '_blank']
													]), // Button
												], // children
											]), // Nav
										]), // ListTile
										new ScrollView([
											'child' => new Column([
												'class' => '-sg-paddingnorm',
												'children' => [
													'referer : '.$item->referer,
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