<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_planning($planningInfo = NULL,$options = NULL) {
	$ret='';

	unset($self->theme->moduleNav);

	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$ui->add('<a href="'.url('project/planning').'" title="หน้าหลัก"><i class="icon -home"></i><span class="">หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('project/my/planning').'" title="แผนงานของฉัน"><i class="icon -person"></i><span class="">ของฉัน</span></a>');

	if ($planningInfo->tpid) {
		$ui->add('<sep>');
		$ui->add('<a href="'.url('project/planning/'.$planningInfo->tpid).'" title="รายละเอียด"><i class="icon -view"></i><span class="">รายละเอียด</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$planningInfo->tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');
	}
	/*
	$dropboxUi=new Ui(NULL,'ui-dropbox');
	$dropboxUi->add('<a href="'.url('project/my/action/*').'" title="กิจกรรมล่าสุด"><i class="icon -person"></i><span>กิจกรรมล่าสุด</span></a>');
	$dropboxUi->add('<a href="'.url('project/my/setting').'" title="ตั้งค่า"><i class="icon -setting"></i><span>ตั้งค่า</span></a>');
	$ui->add(sg_dropbox($dropboxUi->build('ul'),'{class:"leftside -atright"}'));
	*/
	$ret .= $ui->build();
	return $ret;
}
?>