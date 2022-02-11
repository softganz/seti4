<?php
/**
* My Shop Menu
*
* @param Int $shopId
* @return String
*/

$debug = true;

function view_green_my_shop_nav($shopId = NULL) {
	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a href="'.url('green/my/shop').'"><i class="icon -material">account_balance</i><span>ร้านค้า</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/my/land').'" data-webview="ผลผลิตของกลุ่ม/ร้านค้า"><i class="icon -material">nature_people</i><span>ผลผลิต</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/my/goods').'" data-webview="รายชื่อสินค้า"><i class="icon -material">local_florist</i><span>สินค้า</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('green/my/follower').'" data-webview="ผู้ติดตามกลุ่ม/ร้านค้า"><i class="icon -material">stars</i><span>ติดตาม</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="จัดการกลุ่ม/ร้านค้า"><i class="icon -material">settings</i><span>จัดการ</span></a>');

	return Array('main' => $ui);
}
?>