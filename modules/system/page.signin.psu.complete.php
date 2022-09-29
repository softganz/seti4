<?php
/**
* Module  :: Description
* Created :: 2022-09-28
* Modify  :: 2022-09-28
* Version :: 1
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class SigninPsuComplete extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
		parent::__construct();
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Title',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					print_r(post(),1),
				], // children
			]), // Widget
		]);
	}
}
?>