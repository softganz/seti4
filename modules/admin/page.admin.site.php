<?php
/**
* Admin :: Site building
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage admin/site
*/

import('widget:admin.menu.site.php');

class AdminSite extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Site building',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
			'body' => new Container([
				'class' => 'admin-panel',
				'children' => [
					new AdminMenuSiteWidget(),
				], // children
			]), // Widget
		]);
	}
}
?>