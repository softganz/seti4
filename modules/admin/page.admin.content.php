<?php
/**
* Admin   :: Content Management
* Created :: 2016-11-08
* Modify  :: 2024-08-19
* Version :: 2
*
* @return Widget
*
* @usage admin/content
*/

import('widget:admin.menu.content.php');

class AdminContent extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Content management'
			]), // AdminAppBarWidget
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