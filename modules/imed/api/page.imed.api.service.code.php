<?php
/**
* iMed API :: Care Service Code
* Created 2021-07-04
* Modify  2021-07-04
*
* @return Widget
*
* @usage imed/api/servicecode
*/

$debug = true;

import('package:imed/care/models/model.service.menu.php');

class ImedApiServiceCode extends Page {

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return ServiceMenuModel::items();
	}

}
?>