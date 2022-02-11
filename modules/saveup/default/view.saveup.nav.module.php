<?php
/**
* Project Module Navigator
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_saveup_nav_module($self) {
	// Module navigator
	$ui=new Ui('ul','navgroup -main');

	$ui=new Ui(NULL,'ui-nav -info');
	$ui->add('<a href="'.url('saveup').'">หน้าหลัก</a>');
	$ui->add('<a href="'.url('saveup/member').'">สมาชิก</a>');
	$ui->add('<a href="'.url('saveup/gl').'">บัญชี</a>');
	$ui->add('<a href="'.url('saveup/report').'">รายงาน</a>');
	if (user_access('administer saveups')) $ui->add('<a href="'.url('saveup/admin').'">จัดการ</a>');
	
	$ret=$ui->build();
	return $ret;
}
?>