<?php
/**
* Admin :: Content Management
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage admin/content
*/

import('widget:admin.menu.content.php');

class AdminContent extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Content management',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
			'body' => new Container([
				'class' => 'admin-panel',
				'children' => [
					new AdminMenuContentWidget(),
				], // children
			]), // Widget
		]);
	}
}
?>