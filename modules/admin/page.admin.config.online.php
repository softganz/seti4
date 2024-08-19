<?php
/**
* Admin   :: Clear User Online
* Created :: 2023-02-09
* Modify  :: 2024-08-19
* Version :: 3
*
* @return Widget
*
* @usage admin/config/online
*/

class AdminConfigOnline extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Clear user online'
			]), // AdminAppBarWidget
			'body' => new Widget([
				'children' => [
					R::PageWidget('stats.online'),
				], // children
			]), // Widget
		]);
	}
}
?>