<?php
function project_admin_setting($self) {
	R::View('project.toolbar',$self,'Project Setting','admin');
	$self->theme->sidebar=R::View('project.admin.menu','setting');

	$ui = new Ui(NULL,'ui-menu');
	$ui->add('<a href="'.url('project/admin/category').'"><i class="icon -material">category</i><span>Category</span></a>');
	$ui->add('<a href="'.url('project/admin/planning/issue').'"><i class="icon -material">category</i><span>ประเด็นแผนงาน</span></a>');
	$ui->add('<a href="'.url('project/admin/expcode').'"><i class="icon -material">category</i><span>รหัสค่าใช้จ่าย</span></a>');

	//$ui->add('<a href="'.url('project/admin/planning/list').'">รายชื่อแผนงาน</a>');


	if (mydb::table_exists('%project_fund%')) {
		$ui->add('<a href="'.url('project/admin/repair/fund/address').'"><i class="icon -material">account_balance</i><span>กองทุนที่บันทึกที่อยู่ไม่เรียบร้อย</span></a>');
	}
	$ui->add('<a href="'.url('project/admin/repair/proposal/status').'"><i class="icon -material">directions_run</i><span>ปรับปรุงสถานะโครงการที่ผ่านการเป็นโครงการติดตาม</span></a>');

	$ui->add('<sep>');

	$ui->add('<a class="sg-action" href="'.url('code/changwat').'" data-rel="#main"><i class="icon -material">ballot</i><span>รหัสจังหวัด/อำเภอ</span></a>');
	$ui->add('<a href="'.url('project/admin/distance').'"><i class="icon -material">ballot</i><span>ระยะทาง</span></a>');

	$ui->add('<sep>');

	$ui->add('<a href="'.url('project/admin/upgrade').'"><i class="icon -material">build_circle</i><span>Upgrade Database</span></a>');
	$ret .= $ui->build();
	return $ret;
}
?>