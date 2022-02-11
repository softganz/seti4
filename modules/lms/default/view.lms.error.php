<?php
/**
* LMS :: Error
* Created 2020-07-04
* Modify  2020-07-04
*
* @param 
* @return String
*/

$debug = true;

function view_lms_error($self, $error, $message) {
	R::View('toolbar', $self, 'Learning Management System (LMS)', 'lms');

	$ret = '';

	$ret .= message($error, $message);

	return $ret;
}
?>