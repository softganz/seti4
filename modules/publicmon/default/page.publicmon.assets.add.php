<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon_assets_add($self) {
	R::View('publicmon.toolbar',$self,'Create New Assets');

	$ret .= '<h3>Create New Assetd</h3>';

	return $ret;
}
?>