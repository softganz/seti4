<?php
/**
* Admin   :: Site building
* Created :: 2016-11-08
* Modify  :: 2024-08-19
* Version :: 2
*
* @return Widget
*
* @usage admin/site
*/

import('widget:admin.menu.site.php');

class AdminSite extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Site building'
			]), // AdminAppBarWidget
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