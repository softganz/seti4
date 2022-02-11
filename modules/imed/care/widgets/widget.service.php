<?php
/**
* Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Array $args
* @return Widget
*/

$debug = true;

import('package:imed/care/models/model.service.php');

class Service extends Widget {
	var $data = [];

	function __construct($args = []) {
		parent::__construct($args);
	}

	// @override
	function initWidget() {
		$this->data = ServiceModel::services();
	}

	function build() {
		// debugMsg($this,'$this');
		return new Container([
			'children' => [
				'SERVICE',
			], // children
		]);
	}
}
?>