<?php
/**
 * class homepage
 *
 * @param Integer $prid product to be process
 * @param String $action 'delete'=>delete item in shoping cart
 * @return String
 */
function ibuy_cart($self, $prid = NULL, $action = NULL) {
	$self->theme->title = 'ตะกร้า';

	// Create customer record if empty
	if (is_null(i()->am)) {
		mydb::select('INSERT INTO %ibuy_customer% (`uid`, `custtype`) VALUES (:uid, "") ON DUPLICATE KEY UPDATE `uid` = :uid', ':uid', i()->uid);
	}


	$cartInfo = R::Model('ibuy.cart.get');

	if (empty($cartInfo->items)) return message('error', 'ไม่มีรายการสินค้าในตะกร้า');

	if (i()->ok && $prid && $action == 'delete') {
		$stmt = 'DELETE FROM %ibuy_cart% WHERE `tpid` = :tpid AND `uid` = :uid LIMIT 1';
		mydb::query($stmt,':tpid',$prid,':uid',i()->uid);
		return 'ลบรายการเรียบร้อย';
	}

	/*
	if (cfg('ibuy.proceed')->type == 'quick') {
		$ret = R::Page('ibuy.cart.proceed.quick', NULL);
	} else {

	}
	*/

	//$ret .= print_o($cartInfo,'$cartInfo');
	//$ret .= print_o($_SESSION,'$_SESSION');
	//$ret .= print_o($_COOKIE,'$_COOKIE');

	$ui = new Ui();

	$ui->add('<a class="btn" href="'.url('ibuy/product').'"><i class="icon -material -gray">keyboard_arrow_left</i><span>เลือกซื้อสินค้าต่อ</span></a>');
	$ui->add('<a class="btn -primary" href="'.url('ibuy/checkout').'"><i class="icon -material">done_all</i><span>ดำเนินการสั่งซื้อ</span></a>');
	$ret .= '<nav class="nav -page -sg-text-right">'.$ui->build().'</nav>';

	$ret .= R::Page('ibuy.cart.view', $cartInfo);

	$ret .= '<nav class="nav -page -sg-text-right">'.$ui->build().'</nav>';
	return $ret;
}
?>