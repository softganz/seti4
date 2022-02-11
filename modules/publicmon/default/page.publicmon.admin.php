<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon_admin($self) {
	R::View('publicmon.toolbar',$self,'Administrator');
	$ret = '<h3>Administrator</h3>';
	return $ret;
}
?>