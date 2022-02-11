<?php
/**
* LMS : Course Module Information
* Created 2020-08-06
* Modify  2020-08-06
*
* @param Object $self
* @param Object $courseInfo
* @return String
*
* @usage lms/{$courseId}/manage.mod
*/

$debug = true;

function lms_manage_mod_view($self, $courseInfo, $moduldId) {
	R::View('toolbar', $self, 'Manage/Module/'.$courseInfo->name, 'lms', $courseInfo);

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$moduleInfo = $courseInfo->module[$moduldId];

	R::View('toolbar', $self, 'Manage/Module/'.$moduleInfo->name, 'lms', $courseInfo);

	$ret = '';

	$self->theme->sidebar = R::View('lms.manage.menu', $courseInfo);

	$ret .= '<header class="header"><h3>ข้อมูลรายวิชา</h3></header>';

	$tables = new Table();

	$tables->rows[] = array('ชื่อภาษาไทย', $moduleInfo->name);
	$tables->rows[] = array('ชื่อภาษาอังกฤษ', $moduleInfo->enname);

	$ret .= $tables->build();

	$ret .= '<header class="header"><h3>ตารางเรียน</h3></header>';
	$ret .= R::Page('lms.manage.timetable', NULL, $courseInfo, NULL, $moduldId);


	//$ret .= print_o($moduleInfo, '$moduleInfo');
	//$ret .= print_o($courseInfo, '$courseInfo');

	return $ret;
}
?>