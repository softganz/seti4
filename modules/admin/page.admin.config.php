<?php
/**
* Admin :: Site Configuration
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage admin/config
*/

import('widget:admin.menu.config.php');

class AdminConfig extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Site Configuration',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
			'body' => new Container([
				'class' => 'admin-panel',
				'children' => [
					new AdminMenuConfigWidget(),
				], // children
			]), // Widget
		]);
	}
}
?>