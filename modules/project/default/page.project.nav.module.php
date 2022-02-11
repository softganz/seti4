<?php
/**
* Project Module Navigator
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function project_nav_module($self,$rs = NULL,$para = NULL) {
	$tpid=$rs->tpid;
	$isEdit=user_access('administer projects','edit own project content',$rs->uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);
	$ui=new ui(NULL,'ui-nav -main -project');
	$ui->add('<a href="'.url('project').'" title="แผนที่ภาพรวม">แผนที่ภาพรวม</a>');
	$ui->add('<a href="'.url('project/list'.($para->set?'/set/'.$para->set:'')).'" title="รายชื่อโครงการมาใหม่">รายชื่อโครงการ</a>');
	if (projectcfg::enable('develop')) $ui->add('<a href="'.url('project/develop').'" title="รายชื่อโครงการกำลังพัฒนา">พัฒนาโครงการ</a>');
	$ui->add('<a href="'.url('project/report').'" title="วิเคราะห์ สรุป และรายงานต่าง">วิเคราะห์ภาพรวม</a>');
	$ui->add('<a href="'.url('project/manual').'" title="แบบฟอร์ม คู่มือ สื่อ">คู่มือและเอกสาร</a>');
	if (user_access('administer projects')) {
		$ui->add('<a href="'.url('project/admin').'" title="ผู้จัดการระบบ">ผู้จัดการระบบ</a>');
	}
	$ret.=$ui->build();

	return $ret;
}
?>