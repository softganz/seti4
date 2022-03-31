<?php
/**
* Admin :: Logs
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage admin/log
*/

import('widget:admin.menu.log.php');

class AdminLog extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Logs',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
			'body' => new Container([
				'class' => 'admin-panel',
				'children' => [
					new AdminMenuLogWidget(),
				], // children
			]), // Widget
		]);
	}
}
?>