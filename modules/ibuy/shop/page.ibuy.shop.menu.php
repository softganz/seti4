<?php
function ibuy_shop_menu($rs) {
	$shopId=$rs->orgid;
	$ret .= '<header class="header"><h3>หน้าร้าน</h3></header>'._NL;

	$ui = new Ui([
		'type' => 'menu',
		'children' => [
			'<a href="'.url('ibuy/shop/'.$shopId).'">หน้าร้าน</a>',
			'<a href="'.url('ibuy/shop/manage/'.$shopId.'/product').'">จัดการสินค้า</a>',
			//$ui->add('<a href="'.url('ibuy/shop/order').'">คำสั่งซื้อ</a>');
			//$ui->add('<a href="'.url('ibuy/shop/order').'">แจ้งชำระเงิน</a>');
			//$ui->add('<a href="'.url('ibuy/shop/product').'">สินค้า</a>'.($menu=='product'?$submenu:''));
			//$ui->add('<a href="'.url('ibuy/shop/member').'">สมาชิก</a>'.($menu=='member'?$submenu:''));
			//$ui->add('<a href="'.url('ibuy/shop/category').'">หมวดสินค้า</a>');
		],
	]);
	$ret . =$ui->build();

	return $ret;
}
?>