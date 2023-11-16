<?php
/**
* API     :: API Main Page
* Created :: 2023-11-13
* Modify  :: 2023-11-13
* Version :: 1
*
* @param String $arg1
* @return Widget
*
* @usage api
*/

class Api extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		parent::__construct([
			'arg1' => $arg1
		]);
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'API',
			]), // AppBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}
}
?>