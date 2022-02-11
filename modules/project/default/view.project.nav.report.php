<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_report($rs=NULL,$options=NULL) {
	$ret = '';

	$ui = new Ui(NULL,'ui-nav -sg-text-center');

	$ui->add('<a href="'.url('project').'" title="หน้าหลัก"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>', array('class'=>'-home'));
	$ui->add('<a href="'.url('project/report').'" title="วิเคราะห์"><i class="icon -material">assessment</i><span class="">วิเคราะห์</span></a>', array('class'=>'-home'));

	$ret .= $ui->build();

	/*
	$dropboxUi=new Ui(NULL,'ui-dropbox');
	$dropboxUi->add('<a href="'.url('project/my/action/*').'" title="กิจกรรมล่าสุด"><i class="icon -person"></i><span>กิจกรรมล่าสุด</span></a>');
	$dropboxUi->add('<a href="'.url('project/my/setting').'" title="ตั้งค่า"><i class="icon -setting"></i><span>ตั้งค่า</span></a>');
	$ret .= sg_dropbox($dropboxUi->build('ul'),'{class:"leftside -atright"}');
	*/
	return $ret;
}
?>