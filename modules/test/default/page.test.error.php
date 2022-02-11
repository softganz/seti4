<?php
/**
* Module :: Description
* Created 2021-11-22
* Modify  2021-11-22
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class TestError extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Test Error',
			]),
			'body' => new Column([
				'children' => [
					'<a href="'.url('project/proposal/9999999').'">Proposal Not Found</a>',
					'<a class="sg-action" href="'.url('project/proposal/9999999').'" data-rel="box">Proposal Not Found AJAX</a>',
				],
			]),
		]);
	}
}
?>