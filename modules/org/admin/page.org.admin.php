<?php
/**
* Org :: Admin Main Page
* Created 2015-02-14
* Modify  2021-08-15
*
* @return Widget
*
* @usage org/admin
*/

$debug = true;

class OrgAdmin extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Organization Administrator  v'.cfg('org.version'),
			]),
			'sideBar' => new Container([
				'tagName' => 'nav',
				'class' => 'navbar',
				'children' => [
					'<header class="header"><h3>Admin Menu</h3></header>',
					new Ui([
						'type' => 'menu',
						'children' => [
							'<a href="'.url('org/admin/org').'">รายชื่อองค์กร</a>',
							'<a href="'.url('org/admin/meeting').'">รายชื่อกิจกรรม</a>',
							'<a href="'.url('org/admin/officer').'">รายชื่อเจ้าหน้าที่องค์กร</a>',
							'<a href="'.url('org/admin/merge').'">รวมรายชื่อซ้ำ</a>',
							'<sep>',
							'<a href="'.url('org/admin/upgrade').'">Upgrade Database</a>',
						],
					]),
				],
			]),
			'body' => new Container([
				'class' => '-sg-paddingmore',
				'children' => [
					'ยินดีต้อนรับสู่ Organization Administrator',
				],
			]),
		]);
	}
}
?>