<?php
/**
* LMS : Manage Course
* Created 2020-08-06
* Modify  2020-08-06
*
* @param Object $self
* @param Object $courseInfo
* @return String
*
* @usage lms/{$courseId}/manage.course
*/

$debug = true;

function lms_manage_course($self, $courseInfo) {
	R::View('toolbar', $self, 'Manage/Course/'.$courseInfo->name, 'lms', $courseInfo);

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$self->theme->sidebar = R::View('lms.manage.menu', $courseInfo);

	$tables = new Table();

	foreach ($courseInfo->module as $module) {
		$tables->rows[] = array(
			$module->code,
			$module->name.($module->enname ? '<br />'.$module->enname : ''),
			$module->credit.($module->crmean ? ' ('.$module->crmean.')' : ''),
		);
	}
	$tables = new Table();

	foreach ($courseInfo->module as $module) {
		$tables->rows[] = array(
			$module->code,
			$module->name.($module->enname ? '<br />'.$module->enname : ''),
			$module->credit.($module->crmean ? ' ('.$module->crmean.')' : ''),
		);
	}

	// Main Information
	$ret .= '<div id="info" class="" style="overflow: auto;">'
		. '<h5>ชื่อหลักสูตร '.$courseInfo->info->name.($courseInfo->info->enname ? ' ('.$courseInfo->info->enname.')' : '').'</h5>'
		. '<div>'.nl2br($courseInfo->info->detail).'</div>'
		. '<h5>รายวิชา</h5>'
		. $tables->build()
		. '</div><!-- sg-view -->';
	
	//$ret .= print_o($courseInfo, '$courseInfo');

	return $ret;
}
?>