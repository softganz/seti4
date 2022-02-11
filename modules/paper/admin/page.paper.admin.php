<?php
/**
* Module :: Description
* Created 2021-09-15
* Modify  2021-09-15
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class PaperAdmin extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Paper Admin',
			]),
			'body' => new Column([
				'children' => [
					'<a href="'.url('paper/admin/repair/revision').'">Repair Topid and Revision Id</a>',
				],
			]),
		]);
	}
}
?>