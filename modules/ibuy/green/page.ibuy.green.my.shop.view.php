<?php
/**
* Module Method
* Created 2019-10-01
* Modify  2019-10-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_green_my_shop_view($self, $shopInfo) {
	$multipleShop = cfg('ibuy.createMultipleShop');

	$ret = '';

	$isAdmin = user_access('administer ibuys');
	$isAccessDev = in_array(i()->username, array('softganz','momo'));

	$myShopList = R::Model('ibuy.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');

	$ui = new Ui();

	if ($multipleShop || $isAdmin) {
		$ui->add('<a class="sg-action btn -link" href="'.url('ibuy/my/shop/create').'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>เปิดร้านค้าใหม่</span></a>');
	}

	if (count($myShopList) > 1) {
		$ui->add('<a class="btn -primary" href="'.url('ibuy/green/my/shop', array('selectshop' => 'yes')).'"><i class="icon -material">account_balance</i><span>เลือกร้านค้า</span></a>');
	}

	if ($isAdmin) {
		$ui->add('<a class="btn" href="'.url('ibuy/green/my/shop', array('selectshop' => '*')).'"><i class="icon -material">view_list</i><span>ร้านค้าทั้งหมด</span></a>');
	}

	if ($ui->count()) {
		$ret .= '<nav class="nav -page -sg-text-right">'.$ui->build().'</nav>';
	}



	$ret .= '<header class="header"><h3>ยินดีต้อนรับ '.i()->name.'</h3></header>';

	if (empty($shopInfo->info->house) || empty($shopInfo->info->phone)) {
		return $ret.message('notify','กรุณาป้อนข้อมูลร้านค้า (ที่อยู่ , โทรศัพท์) ให้ครบถ้วนด้วยค่ะ').R::Page('ibuy.green.my.manage', $self);
	}




	if ($isAccessDev) {
		$actionUi = new Ui(NULL, '-sg-flex');
		$actionUi->addConfig('nav', '{class: "nav -app-menu"}');
		$actionUi->header('<h3>สวนยางยั่งยืน</h3>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/rubber/land').'" data-webview="แปลงสวนยาง"><i class="icon -material">nature_people</i><span>แปลงสวนยาง</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/tree').'" data-webview="ธนาคารต้นไม้"><i class="icon -material">nature</i><span>ธนาคารต้นไม้</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/cotree').'" data-webview="พืชร่วมยาง"><i class="icon -material">local_florist</i><span>พืชร่วมยาง</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/samtree').'" data-webview="พืชแซมยาง"><i class="icon -material">grass</i><span>พืชแซมยาง</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/animal').'" data-webview="ปศุสัตว์"><i class="icon -material">emoji_nature</i><span>ปศุสัตว์</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/rubber/buy').'" data-webview="รับซื้อน้ำยาง"><i class="icon -material">money</i><span>รับซื้อน้ำยาง</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('ibuy/green/my/glorg').'" data-webview="บัญชีต้นทุน"><i class="icon -material">attach_money</i><span>บัญชีต้นทุน</span></a>');

		$ret .= $actionUi->build();
	}



	$ret .= '<div class="ibuy-shop-qrcode -sg-text-center" style="margin: 32px auto; padding: 16px 0;">'
		. '<h3>QR-Code องค์กร/หน้าร้าน</h3>'
		. '<div style="margin: 0 auto; width: 240px;">'
		. SG\qrcode(url('ibuy/green/shop/'.$shopInfo->shopId))
		. '</div>'
		. '</div>';

	//$ret .= print_o($shopInfo, '$shopInfo');

	/*
	$ret .= '<div>';
	if ($isAdmin) $ret .= '<p>Shop ID '.$shopInfo->shopId.'</p>';
	$ret .= '<p>ที่อยู่ '.$shopInfo->info->house.'</p>';
	$ret .= '<p>โทรศัพท์ '.$shopInfo->info->phone.'</p>';
	$ret .= '<p>เว็บไซต์ '.$shopInfo->info->website.'</p>';
	$ret .= '<p>อีเมล์ '.$shopInfo->info->email.'</p>';

	$ret .= print_o($shopInfo, '$shopInfo');
	$ret .= '</div>';
	*/


	/*
	if ($isViewOnly) {
		// Do nothing
	} else if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('ibuy/green/my/shop',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
	} else if ($isEditable) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('ibuy/green/my/shop/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}
	*/

	$ret .= '<style type="text/css">
	.nav.-app-menu {text-align: center;}
	.nav.-app-menu .header {flex: 1 0 100%; text-align: center; padding: 8px 0;}
	.nav.-app-menu .header>h3 {padding: 0;}
	.nav.-app-menu>ul>li {flex: 1 0 100%; margin: 8px 0;}
	.nav.-app-menu>ul>li>a {padding: 8px 0; display: block; background-color: #fff;}
	.nav.-app-menu>ul>li>a>.icon {display: block; margin: 0 auto;}

	@media (min-width:48em) {		/* 768/16 = 48 */
		.nav.-app-menu>ul>li {flex: 0 0 128px;}
	}
	</style>';
	return $ret;
}
?>