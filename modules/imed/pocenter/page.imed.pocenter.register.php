<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter_register($self) {
	R::View('imed.toolbar', $self, 'ลงทะเบียน - ศูนย์กายอุปกรณ์', 'pocenter', $orgInfo);

	$ret = '';

	$ret .= 'FORM FOR REGISTER';
	return $ret;
}
?>