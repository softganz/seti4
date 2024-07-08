<?php
/**
* Admin   :: Ban Management
* Created :: 2024-07-08
* Modify  :: 2024-07-08
* Version :: 1
*
* @return Widget
*
* @usage module/{id}/method
*/

class AdminBan extends Page {
	function __construct() {
		parent::__construct([
		]);
	}

	function rightToBuild() {return true;}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Ban Management',
			]), // AppBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}

	function list() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Ban List',
				'boxHeader' => true,
			]),
			'body' => new Table([
				'thead' => ['IP/Host', 'Start Time', 'End Time', ''],
				'children' => array_map(
					function($ban, $key) {
						return [
							$ban->ip.$ban->host,
							$ban->start,
							$ban->end,
							new Nav([
								'children' => [
									new Button([
										// 'href' => url(),
										'class' => '-disabled',
										'icon' => new Icon('edit'),
									]),
									new Button([
										'class' => 'sg-action',
										'href' => url('api/admin/ban/remove', ['id' => $key]),
										'icon' => new Icon('cancel'),
										'rel' => 'none',
										'done' => 'remove: parent tr',
										'attribute' => [
											'data-title' => 'ลบรายการ',
											'data-confirm' => 'ลบรายการ กรุณายืนยัน?'
										]
									])
								], // children
							]), // Nav
						];
					},
					(Array) cfg('ban.ip'),
					array_keys((Array) cfg('ban.ip'))
				)
			])
		]);
	}
}
?>