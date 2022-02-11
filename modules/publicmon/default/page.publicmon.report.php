<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon_report($self) {
	R::View('publicmon.toolbar',$self,'Report');
	$ret = '<h3>Public Monitor Report</h3>';
	return $ret;
}
?>