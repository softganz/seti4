<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_my($rs=NULL,$options=NULL) {
	$ret='';

	unset($self->theme->moduleNav);

	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$ui->add('<a href="'.url('project/my').'" title="หน้าหลัก"><i class="icon -home"></i><span class="">หน้าหลัก</span></a>', array('class'=>'-home'));
	$ui->add('<a href="'.url('project/my/action/list').'" title="บันทึกกิจกรรม"><i class="icon -assignment"></i><span class="">กิจกรรม</span></a>', array('class'=>'-action'));
	$ui->add('<a href="'.url('project/my/planning').'" title="แผนงาน"><i class="icon -diagram"></i><span class="">แผนงาน</span></a>', array('class'=>'-planning'));
	$ui->add('<a href="'.url('project/my/set').'" title="ชุดโครงการ"><i class="icon -diagram"></i><span class="">ชุดโครงการ</span></a>', array('class'=>'-set'));
	$ui->add('<a href="'.url('project/my/develop').'" title="พัฒนาโครงการ"><i class="icon -nature-people"></i><span class="">พัฒนา</span></a>', array('class'=>'-proposal'));
	$ui->add('<a href="'.url('project/my/project').'" title="ติดตามโครงการ"><i class="icon -walk"></i><span class="x-hidden">ติดตาม</span></a>', array('class'=>'-follow'));
	$ret.=$ui->build();

	$dropboxUi=new Ui(NULL,'ui-dropbox');
	$dropboxUi->add('<a href="'.url('project/my/action/*').'" title="กิจกรรมล่าสุด"><i class="icon -person"></i><span>กิจกรรมล่าสุด</span></a>');
	$dropboxUi->add('<a href="'.url('project/my/setting').'" title="ตั้งค่า"><i class="icon -setting"></i><span>ตั้งค่า</span></a>');
	$ret .= sg_dropbox($dropboxUi->build('ul'),'{class:"leftside -atright"}');
	return $ret;
}
?>