<?php
function ibuy_admin_setting($self) {
	$self->theme->title='Setting';
	$self->theme->sidebar=R::Page('ibuy.admin.menu','setting');

	$ret.='<h3>กำหนดค่าระบบ</h3>';

	$ui=new ui();
	if (cfg('ibuy.price.franchise')) {
		$ui->add('<a href="'.url('ibuy/manage/generate/code').'">Generate Franchise Register Code</a>');
		$ui->add('<a href="'.url('ibuy/manage/monthly/process').'">ประมวลผลประจำเดือน</a>');
	}
	$ui->add('<a href="'.url('ibuy/manage/config').'">Setting</a>');
	$ui->add('<a href="'.url('ibuy/admin/upgrade').'">Upgrade System</a>');
	$ui->add('<a href="'.url('admin').'">จัดการเว็บไซท์</a>');
	$ret.	$ret.=$ui->build('ul');
	return $ret;
}
?>