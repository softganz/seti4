<?php
/**
* iMed Org
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function view_imed_org_setting($orgInfo) {
	$orgId = $orgInfo->orgid;

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;

	$ret .= '<h2>SETTING</h2>';


	return $ret;
}
?>