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
	$ui->add('<a href="'.url('project/my').'" title="หน้าหลัก"><i class="icon -home"></i><span class="">หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('project/my/action/list').'" title="บันทึกกิจกรรม"><i class="icon -assignment"></i><span class="">กิจกรรม</span></a>');
	$ui->add('<a href="'.url('project/tree',array('uid'=>i()->uid)).'" title="แผนผังโครงการ"><i class="icon -list"></i><span class="">แผนผังโครงการ</span></a>');

	//$ui->add('<a href="'.url('project/my/planning').'" title="แผนงาน"><i class="icon -diagram"></i><span class="">แผนงาน</span></a>');
	//$ui->add('<a href="'.url('project/my/develop').'" title="พัฒนาโครงการ"><i class="icon -nature-people"></i><span class="">พัฒนา</span></a>');
	//$ui->add('<a href="'.url('project/my/project').'" title="ติดตามโครงการ"><i class="icon -walk"></i><span class="x-hidden">ติดตาม</span></a>');

	//$ui->add('<a href="'.url('project/my/eval').'" title="ติดตาม/ประเมินผลโครงการ"><i class="icon -walk"></i><span class="">ติดตาม/ประเมินผล</span></a>');
	$ui->add('<a href="'.url('project/my/qt').'" title="แบบฟอร์มประเมิน"><i class="icon -description"></i><span class="">แบบฟอร์มประเมิน</span></a>');


	$dropboxUi=new Ui(NULL,'ui-dropbox');
	$dropboxUi->add('<a href="'.url('project/my/action/*').'" title="กิจกรรมล่าสุด"><i class="icon -person"></i><span>กิจกรรมล่าสุด</span></a>');
	$dropboxUi->add('<a href="'.url('project/my/setting').'" title="ตั้งค่า"><i class="icon -setting"></i><span>ตั้งค่า</span></a>');
	$ui->add(sg_dropbox($dropboxUi->build('ul'),'{class:"leftside -atright"}'));
	$ret.=$ui->build();
	return $ret;
}
?>