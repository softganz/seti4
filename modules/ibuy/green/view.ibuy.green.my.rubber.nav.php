<?php
/**
* My Rubber Menu
*
* @param Int $shopId
* @return String
*/

$debug = true;

function view_ibuy_green_my_rubber_nav($shopId = NULL) {
	$ui = new Ui();

	$ui->addConfig('nav', '{class: "nav -page -app-icon"}');

	$ui->add('<a href="'.url('ibuy/green/my/shop').'"><i class="icon -material">account_balance</i><span>องค์กร</span></a>');
	$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/rubber/land').'"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action -btn-hot" href="'.url('ibuy/my/land/form').'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่ม</span></a>');

	//$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/rubber/land').'"><i class="icon -material">nature_people</i><span>เพิ่มแปลง</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/rubber').'" data-webview="รายชื่อสินค้า"><i class="icon -material">local_florist</i><span>ซื้อน้ำยาง</span></a>');

	$ret = $ui->build()._NL;

	return $ret;
}
?>