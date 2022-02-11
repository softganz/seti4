<?php
/**
* iMed :: Psychiatry Patient Information Page Controller
* Created 2021-05-26
* Modify  2021-05-31
*
* @return Widget
*
* @usage imed/psyc/info
*/

$debug = true;

class ImedPsycInfo {
	function __construct() {}

	function build() {
		debugMsg('Psychiatry Patient Info');
		return new Widget([
		]);
	}
}
?>