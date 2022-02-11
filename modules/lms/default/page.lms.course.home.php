<?php
/**
* LMS : Course Home Page
* Created 2020-08-05
* Modify  2020-08-06
*
* @param Object $self
* @param Object $courseInfo
* @return String
*/

$debug = true;

function lms_course_home($self, $courseInfo) {
	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');
	$ret = '';
	
	$homeInfo = R::Model('lms.course.homepage.get', $courseId);

	if ($homeInfo->html) {
		$ret .= eval_php($homeInfo->html);
	} else {
		$ret .= R::Page('lms.course.view', $self, $courseInfo);
	}

	return $ret;
}
?>