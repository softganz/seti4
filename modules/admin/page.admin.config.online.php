<?php
/**
* Admin   :: Clear User Online
* Created :: 2023-02-09
* Modify  :: 2023-02-09
* Version :: 2
*
* @return Widget
*
* @usage admin/config/online
*/

class AdminConfigOnline extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Clear user online',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					R::PageWidget('stats.online'),
				], // children
			]), // Widget
		]);
	}
}
?>