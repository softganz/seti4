<?php
/**
* LMS :: Admin
* Created 2020-07-04
* Modify  2020-07-04
*
* @param Object $self
* @return String
*/

$debug = true;

function lms_admin($self) {
	R::View('toolbar', $self, 'LMS/Admin', 'lms', NULL, '{searchform: false}');

	$ret = '';
	return $ret;
}
?>