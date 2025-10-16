<?php
/**
* Admin   :: Logs
* Created ::2016-11-08
* Modify  :: 2024-08-19
* Version :: 2
*
* @return Widget
*
* @usage admin/log
*/

import('widget:admin.menu.log.php');

class AdminLog extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Logs'
			]), // AdminAppBarWidget
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