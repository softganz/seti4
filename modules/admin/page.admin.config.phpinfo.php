<?php
/**
* Module :: Description
* Created 2021-10-04
* Modify  2021-10-04
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class AdminConfigPhpinfo extends Page {
	function build() {
		phpinfo();
		die;
	}
}
?>