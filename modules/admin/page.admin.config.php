<?php
/**
* Admin   :: Site Configuration
* Created :: 2016-11-08
* Modify  :: 2024-08-19
* Version :: 2
*
* @return Widget
*
* @usage admin/config
*/

import('widget:admin.menu.config.php');

class AdminConfig extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Site Configuration'
			]), // AdminAppBarWidget
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