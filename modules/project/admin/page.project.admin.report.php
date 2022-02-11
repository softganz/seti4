<?php
/**
* Project :: Admin Report Menu
* Created 2019-10-13
* Modify  2020-08-04
*
* @param Object $self
* @return String
*/

$debug = true;

function project_admin_report($self) {
	R::View('project.toolbar',$self,'Administrator Report','admin');
	$self->theme->sidebar=R::View('project.admin.menu','report');

	$ui = new Ui(NULL, 'ui-menu');

	$ui->add('<a href="'.url('project/admin/report/trainer').'"><i class="icon -material">article</i><span>รายงานบันทึกพี่เลี้ยง</span></a>');
	$ui->add('<a href="'.url('project/admin/report/risk').'"><i class="icon -material">report_problem</i><span>ระดับความเสี่ยงของโครงการ</span></a>');
	$ui->add('<a href="'.url('project/admin/report/calendar').'"><i class="icon -material">assignment</i><span>รายงานปฏิทินกิจกรรม</span></a>');
	$ui->add('<a href="'.url('project/admin/report/checkdata').'"><i class="icon -material">error</i><span>ตรวจสอบข้อมูลผิดพลาด</span></a>');

	$ui->add('<a href="'.url('project/admin/fund/checkdata').'"><i class="icon -material">error</i><span>ตรวจสอบข้อมูลกองทุนผิดพลาด</span></a>');


	$ret .= $ui->build();

	return $ret;
}
?>