<?php
/**
* LMS :: View Course Information
* Created 2020-07-01
* Modify  2020-07-01
*
* @param Object $self
* @param Object $courseInfo
* @return String
*/

$debug = true;

function lms_course_view($self, $courseInfo) {
	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$isAdmin = user_access('administer lms');
	$isTeacher = user_access('teacher lms');

	$currentDate = date('Y-m-d');
	$currentDateTime = date('Y-m-d H:i:s');

	$isRegisterDate = $courseInfo->info->dateregfrom && $courseInfo->info->dateregend && $currentDate >= $courseInfo->info->dateregfrom && $currentDate <= $courseInfo->info->dateregend;

	$isOpenDate = $courseInfo->info->datebegin && $courseInfo->info->dateend && $currentDate >= $courseInfo->info->datebegin && $currentDate <= $courseInfo->info->dateend;

	$isCheckInDate = $courseInfo->info->datebegin && $courseInfo->info->dateend && $currentDateTime >= $courseInfo->info->datebegin && $currentDateTime <= $courseInfo->info->dateend;

	R::View('toolbar', $self, 'หลักสูตร '.$courseInfo->name, 'lms', $courseInfo, '{searchform: false}');

	$ret = '';


	$cardStr = '<header class="header"><h3>หลักสูตร '.$courseInfo->name.'</h3></header>';

	$menu = new Ui(NULL, 'ui-menu');
	$menu->add('<a href="'.url('lms/'.$courseId).'"><i class="icon -material">find_in_page</i><span>รายละเอียดหลักสูตร</span></a>');
	$menu->add('<a><i class="icon -material">supervisor_account</i><span>อาจารย์ประจำหลักสูตร</span></a>');
	$menu->add('<a class="sg-action" href="'.url('lms/'.$courseId.'/course.student').'" data-rel="#info"><i class="icon -material">how_to_reg</i><span>นักศึกษาปัจจุบัน</span></a>');
	$menu->add('<a href="'.url('lms/'.$courseId.'/course.timetable').'"><i class="icon -material">find_in_page</i><span>ตารางเรียน</span></a>');
	$menu->add('<a class="sg-action" href="'.url('lms/'.$courseId.'/course.alumni').'" data-rel="#info"><i class="icon -material">people</i><span>ทำเนียบศิษย์เก่า</span></a>');

	if ($isTeacher) {
		$menu->add('<sep>');
		$menu->add('<a href="'.url('lms/'.$courseId.'/checkin.teacher').'"><i class="icon -material">login</i><span>เช็คอินโดยอาจารย์</span></a>');
	}

	if ($isAdmin) {
		$menu->add('<sep>');
		$menu->add('<a href="'.url('lms/'.$courseId.'/checkin.teacher').'"><i class="icon -material">login</i><span>เช็คอินโดยผู้จัดการระบบ</span></a>');
		$menu->add('<a class="sg-action" href="'.url('lms/'.$courseId.'/course.survey').'" data-rel="#info"><i class="icon -material">done_outline</i><span>ประเมินผลหลักสูตร</span></a>');
		$menu->add('<a href="'.url('lms/'.$courseId.'/manage').'"><i class="icon -material">settings</i><span>จัดการหลักสูตร</span></a>');
	}

	$cardStr .= '<div class="detail sg-view -co-2">';

	$tables = new Table();

	foreach ($courseInfo->module as $module) {
		$tables->rows[] = array(
			$module->code,
			$module->name.($module->enname ? '<br />'.$module->enname : ''),
			$module->credit.($module->crmean ? ' ('.$module->crmean.')' : ''),
		);
	}

	// Main Information
	$cardStr .= '<div id="info" class="-sg-view" style="overflow: auto;">'
		. '<h5>ชื่อหลักสูตร '.$courseInfo->info->name.($courseInfo->info->enname ? ' ('.$courseInfo->info->enname.')' : '').'</h5>'
		. '<div>'.nl2br($courseInfo->info->detail).'</div>'
		. '<h5>รายวิชา</h5>'
		. $tables->build()
		. '</div><!-- sg-view -->';

	// Side bar
	$cardStr .= '<div class="-sg-view">'
		. ($isRegisterDate ? '<nav><a class="btn -primary -fill"><i class="icon -material">how_to_reg</i><span>ลงทะเบียน</span></a></nav>' : '')
		. ($isCheckInDate ? '<nav><a class="btn -primary -fill" href="'.url('lms/'.$courseId.'/checkin').'"><i class="icon -material">login</i><span>เช็คอินเข้าเรียน</span></a></nav>' : '')
		. ($isOpenDate ? '<nav><a class="sg-action btn -primary -fill" href="'.url('lms/'.$courseId.'/course.survey').'" data-rel="#info"><i class="icon -material">done_outline</i><span>ประเมินผลหลักสูตร</span></a></nav>' : '')
		. $menu->build()
		. '</div><!-- sg-view -->';

	$cardStr .= '</div><!-- detail -->';

	//$cardStr .= '<nav class="nav -card">&nbsp;</nav>';


	/*
	$cardUi = new Ui('div', 'ui-card lms-course-view');
	$cardUi->add($cardStr);
	$ret .= $cardUi->build();
	*/

	$ret .= '<div class="lms-course-view">'.$cardStr.'</div>';

	//$ret .= print_o($courseInfo, '$courseInfo');

	$ret .= '<style tyle="text/css">
	.lms-home>.header {margin: 0;}
	.lms-home>.header>h3 {background-color: #eee; margin: 0;}
	.lms-course-view .detail.sg-view {padding: 0; border-bottom: 1px #eee solid;}
	.lms-course-view>.ui-item>.detail>.-sg-view:first-child {padding: 0 16px 16px 16px;}
	.lms-course-view .lms-mod-survey-form-start .btn.-cancel {display: none;}
	</style>';

	return $ret;
}
?>