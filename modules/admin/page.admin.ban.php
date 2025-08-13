<?php
/**
* Admin   :: Ban Management
* Created :: 2024-07-08
* Modify  :: 2025-08-13
* Version :: 2
*
* @return Widget
*
* @usage module/{id}/method
*/

class AdminBan extends Page {
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
				'class' => '-center',
				'thead' => ['IP/Host', 'Start Time', 'End Time', 'icons -i1' => ''],
				'children' => array_map(
					function($ban, $key) {
						return [
							$ban->ip.$ban->host,
							$ban->start,
							$ban->end,
							new Nav([
								'children' => [
									// new Button([
									// 	'type' => 'secondary',
									// 	// 'href' => url(),
									// 	'class' => '-disabled',
									// 	'icon' => new Icon('edit'),
									// ]),
									new Button([
										'type' => 'danger',
										'class' => 'sg-action',
										'href' => url('api/admin/ban/remove', ['id' => $key]),
										'icon' => new Icon('clear'),
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