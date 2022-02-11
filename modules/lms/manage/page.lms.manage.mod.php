<?php
/**
* LMS : Course Module
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

function lms_manage_mod($self, $courseInfo) {
	R::View('toolbar', $self, 'Manage/Module/'.$courseInfo->name, 'lms', $courseInfo);

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$self->theme->sidebar = R::View('lms.manage.menu', $courseInfo);

	$tables = new Table();

	foreach ($courseInfo->module as $module) {
		$tables->rows[] = array(
			$module->code,
			$module->name.($module->enname ? '<br />'.$module->enname : ''),
			$module->credit.($module->crmean ? ' ('.$module->crmean.')' : ''),
			'<a href="'.url('lms/'.$courseId.'/manage.mod.view/'.$module->modid).'"><i class="icon -material">find_in_page</i></a>'
		);
	}

	// Main Information
	$ret .= '<div id="info" class="sg-view">'
		. '<header class="header"><h3>รายวิชา</h3></header>'
		. '<div class="-sg-view">'
		. $tables->build()
		. '</div>'
		. '</div><!-- info -->';
	
	//$ret .= print_o($courseInfo, '$courseInfo');

	return $ret;
}
?>