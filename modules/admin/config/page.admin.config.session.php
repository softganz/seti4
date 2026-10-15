<?php
/**
* Admin   :: View Session Value
* Created :: 2023-02-09
* Modify  :: 2024-08-19
* Version :: 3
*
* @return Widget
*
* @usage admin/config/session
*/

class AdminConfigSession extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'View session value'
			]), // AdminAppBarWidget
			'body' => new ScrollView([
				'children' => [
					print_o($_SESSION,'$_SESSION')
				], // children
			]), // Widget
		]);
	}
}
?>