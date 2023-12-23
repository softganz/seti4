<?php
/**
* Admin   :: View Session Value
* Created :: 2023-02-09
* Modify  :: 2023-02-09
* Version :: 2
*
* @return Widget
*
* @usage admin/config/session
*/

class AdminConfigSession extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'View session value',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
			'body' => new ScrollView([
				'children' => [
					print_o($_SESSION,'$_SESSION')
				], // children
			]), // Widget
		]);
	}
}
?>