<?php
/**
* ERP :: ERP Main Page
* Created 2021-12-01
* Modify  2021-12-01
*
* @param String $arg1
* @return Widget
*
* @usage erp/{id}/action
*/

class ErpHome extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ERP',
			]),
			'body' => new Widget([
				'children' => [
					'WELCOME TO ERP SYSTEM',
				],
			]),
		]);
	}
}
?>