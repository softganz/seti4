<?php
/**
* My Shop Menu
*
* @param Int $shopId
* @return String
*/

$debug = true;

function view_ibuy_green_shop_nav($shopInfo = NULL) {
	$shopId = $shopInfo->shopId;

	$ret = '';
	// Show main navigator

	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav -page -app-icon"}');

	if ($shopId) {
		$ui->add('<a class="sg-action" href="'.url('ibuy/green/shop/'.$shopId).'" data-webview="กลุ่ม/ร้านค้า"><i class="icon -material">account_balance</i><span>ร้านค้า</span></a>');
		$ui->add('<a class="sg-action" href="'.url('ibuy/green/shop/'.$shopId.'/plant').'" data-webview="ผลผลิตของกลุ่ม/ร้านค้า"><i class="icon -material">nature_people</i><span>ผลผลิต</span></a>');
		$ui->add('<a class="sg-action" href="'.url('ibuy/green/shop/'.$shopId.'/land').'" data-webview="แปลงผลิต"><i class="icon -material">nature</i><span>แปลงผลิต</span></a>');
		$ui->add('<a class="sg-action" href="'.url('ibuy/green/shop/'.$shopId.'/goods').'" data-webview="สินค้า"><i class="icon -material">nature</i><span>สินค้า</span></a>');
	} else {
		$ui->add('<a class="sg-action" href="'.url('ibuy/green/shop').'" data-webview="กลุ่ม/ร้านค้า"><i class="icon -material">account_balance</i><span>ร้านค้า</span></a>');
		$ui->add('<a class="sg-action" href="'.url('ibuy/green/land').'" data-webview="แปลงผลิต"><i class="icon -material">nature</i><span>แปลงผลิต</span></a>');
		$ui->add('<a class="sg-action" href="'.url('ibuy/green/plant').'" data-webview="ผลผลิตของกลุ่ม/ร้านค้า"><i class="icon -material">nature_people</i><span>ผลผลิต</span></a>');
	}
	//$ui->add('<a class="sg-action" href="'.url('ibuy/green/plant/').'" data-webview="ผู้ติดตามร้านค้า"><i class="icon -material">stars</i><span>ติดตาม</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/manage').'" data-webview="จัดการกลุ่ม/ร้านค้า"><i class="icon -material">settings</i><span>จัดการ</span></a>');
	$ret .= $ui->build()._NL;

	//$ret .= print_o($shopInfo, '$shopInfo');
	return $ret;
}
?>