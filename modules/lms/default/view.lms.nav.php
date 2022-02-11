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

function view_lms_nav($info = NULL, $options = '{}') {
	$courseId = $info->courseId;

	$ret = '';

	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$dropboxUi = new Ui();

	$ui->add('<a href="'.url('lms').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>','{class: "-home"}');

	$ui->add('<sep>');

	if ($courseId) $ui->add('<a href="'.url('lms/'.$courseId).'"><i class="icon -material">account_balance</i><span>หลักสูตร</span></a>');
	$ui->add('<a href="'.url('lms/checkin').'"><i class="icon -material">login</i><span>เช็คอิน</span></a>');
	$ui->add('<a href="'.url('lms/student/survey').'"><i class="icon -material">trending_up</i><span>แบบประเมิน</span></a>');

	if (user_access('administrator lms')) {
		$ui->add('<a href="'.url('lms/admin').'"><i class="icon -material">settings</i><span>ผู้จัดการระบบ</span></a>');
	}
	
	$ret .= $ui->build();

	if ($dropboxUi->count()) $ret .= sg_dropbox($dropboxUi->show('ul'),'{class:"leftside -atright"}');

	return $ret;
}
?>