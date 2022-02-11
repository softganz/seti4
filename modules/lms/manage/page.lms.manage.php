<?php
/**
* Module Method
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function lms_manage($self, $courseInfo) {
	R::View('toolbar', $self, 'Manage/'.$courseInfo->name, 'lms', $courseInfo);

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$self->theme->sidebar = R::View('lms.manage.menu', $courseInfo);

	return $ret;
}
?>