<?php
/**
* Garage :: Admin Menu
* Created 2020-07-23
* Modify  2020-07-23
*
* @param Object $self
* @return String
*/

$debug = true;

function view_garage_admin_menu($menu = NULL) {
	$ui = new Ui(NULL, 'ui-menu');
	$ui->addConfig('nav', '{class: "nav"}');
	$ui->header('<h3>ผู้จัดการระบบ</h3>');

	$ui->add('<a href="'.url('garage/admin').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('garage/admin/user').'"><i class="icon -material">supervised_user_circle</i><span>สมาชิก</span></a>');
	$ui->add('<a href="'.url('garage/admin/shop').'"><i class="icon -material">account_balance</i><span>ร้านค้า</span></a>');
	$ui->add('<a href="'.url('garage/admin/report').'"><i class="icon -material">trending_up</i><span>รายงาน</span></a>');
	$ui->add('<a href="'.url('garage/admin/setting').'"><i class="icon -material">settings</i><span>กำหนดค่าระบบ</span></a>');

	$ret .= $ui->build();

	return $ret;
}
?>