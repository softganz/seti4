<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon_manage($self) {
	R::View('publicmon.toolbar',$self,'Management');
	$ret = '<h3>Management</h3>';

	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('publicmon/assets').'" data-webview="Assets Management">Assets Management</a>');
	$ui->add('<a class="sg-action" href="'.url('publicmon/member').'" data-webview="Member Management">Member Management</a>');
	$ret .= $ui->build();
	return $ret;
}
?>