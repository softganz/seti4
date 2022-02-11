<?php
/**
 * Report home page
 * 
 */
function icar_admin($self) {
	$self->theme->title = 'รายงาน';
	R::View('icar.toolbar', $self);

	$ui = new Ui();
	$ui->add('<a href="'.url('icar/admin/cost').'">รายงานการบันทึกต้นทุน</a>');
	$ui->add('<a href="'.url('icar/admin/buy').'">รายงานการซื้อรถ</a>');
	$ui->add('<a href="'.url('icar/admin/sale').'">รายงานการขายรถ</a>');
	$ui->add('<a href="'.url('icar/admin/instock').'">รายงานรถคงค้าง</a>');
	$ui->add('<a href="'.url('icar/admin/unpaiddown').'">รายงานเงินดาวน์ค้างชำระ</a>');
	$ui->add('<sep>');
	$ui->add('<a href="'.url('icar/admin/user').'">User List</a>');
	$ui->add('<a href="'.url('icar/admin/shop/create').'">Create new show</a>');

	$ret .= $ui->build('ul');
	return $ret;
}
?>