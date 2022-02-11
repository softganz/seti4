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
	$ui->add('<a href="'.url('project/planning').'" title="หน้าหลัก"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>');

	if ($planningInfo->tpid) {
		$ui->add('<sep>');
	} else {
		$ui->add('<a href="'.url('project/planning/summary').'" title="ภาพรรวม"><i class="icon -material">dashboard</i><span class="">ภาพรวม</span></a>');
		$ui->add('<a href="'.url('project/planning/situation').'" title="สถานการณ์"><i class="icon -material">trending_up</i><span class="">สถานการณ์</span></a>');
		$ui->add('<a href="'.url('project/planning/ampur').'" title="แผนงานกองทุนระดับอำเภอ"><i class="icon -material">fact_check</i><span class="">แผนอำเภอ</span></a>');
		$ui->add('<a href="'.url('project/planning/area').'" title="แผนงานกองทุนระดับพื้นที่"><i class="icon -material">fact_check</i><span class="">แผนพื้นที่</span></a>');
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