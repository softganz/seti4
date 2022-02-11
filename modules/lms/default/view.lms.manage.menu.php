<?php
/**
* LMS :: Default Toolbar Navigation
* Created 2020-07-04
* Modify  2020-07-04
*
* @param Object $info
* @return String
*/

$debug = true;

function view_lms_manage_menu($info = NULL, $options = '{}') {
	$courseId = $info->courseId;

	$ret = '';

	$ui = new Ui(NULL,'ui-menu');

	$ui->add('<a href="'.url('lms/'.$courseId.'/course.view').'"><i class="icon -material">find_in_page</i><span>รายละเอียดหลักสูตร</span></a>');
	$ui->add('<a><i class="icon -material">supervisor_account</i><span>อาจารย์ประจำหลักสูตร</span></a>');
	$ui->add('<a class="sg-action" href="'.url('lms/'.$courseId.'/course.student').'" data-rel="#main"><i class="icon -material">how_to_reg</i><span>นักศึกษาปัจจุบัน</span></a>');
	$ui->add('<a class="sg-action" href="'.url('lms/'.$courseId.'/course.alumni').'" data-rel="#main"><i class="icon -material">people</i><span>ทำเนียบศิษย์เก่า</span></a>');

	$ui->add('<sep>');
	$ui->add('<a class="sg-action" href="'.url('lms/'.$courseId.'/checkin.teacher').'" data-rel="#main"><i class="icon -material">login</i><span>เช็คอิน</span></a>');

	$ui->add('<sep>');

	$ui->add('<a href="'.url('lms/'.$courseId.'/manage.course').'"><i class="icon -material">subject</i><span>หลักสูตร</span></a>');
	$ui->add('<a href="'.url('lms/'.$courseId.'/manage.mod').'"><i class="icon -material">subject</i><span>รายวิชา</span></a>');
	$ui->add('<a href="'.url('lms/'.$courseId.'/manage.timetable').'"><i class="icon -material">table_view</i><span>ตารางเรียน</span></a>');

	$ui->add('<sep>');

	$ui->add('<a href="'.url('lms/'.$courseId.'/manage.homepage').'"><i class="icon -material">account_balance</i><span>หน้าแรกหลักสูตร</span></a>');
	$ui->add('<a href="'.url('lms/'.$courseId.'/manage.navigator').'"><i class="icon -material">navigation</i><span>Top Navigator</span></a>');

	
	$ret .= $ui->build();

	return $ret;
}
?>