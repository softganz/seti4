<?php
/**
* LMS :: View Module Information
* Created 2020-07-01
* Modify  2020-07-01
*
* @param Object $self
* @param Int $moduleInfo
* @return String
*/

$debug = true;

function lms_mod_info_view($self, $moduleInfo) {
	$ret = '';

	$ret .= '<header class="header"><h3>Module Information</h3></header>';

	return $ret;
}
?>