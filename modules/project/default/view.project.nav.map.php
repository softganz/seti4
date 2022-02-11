<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_map($rs=NULL,$options=NULL) {
	$ret='';

	unset($self->theme->moduleNav);

	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$ui->add('<a href="'.url('project').'" title="หน้าหลัก"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('project/list').'"><i class="icon -list"></i><span class="">รายชื่อโครงการ</span></a>');
	$ui->add('<a href="'.url('project/map').'" title="แผนที่โครงการ"><i class="icon -material">room</i><span class="">แผนที่</span></a>');
	$ret.=$ui->build();
	return $ret;
}
?>