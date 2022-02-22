<?php
/**
* Module :: Description
* Created 2022-02-21
* Modify  2022-02-21
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class OrgPaMap extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'PA Network Mapping',
			]), // AppBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}
}
?>