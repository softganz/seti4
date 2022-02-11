<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function green_shop_view($self, $shopInfo) {
	$shopId = $shopInfo->shopId;

	if (!$shopId) return message('error', 'PROCESS ERROR');

	$ret = '';

	$shopUrl = url('green/shop/'.$shopId);
	$shopBanner = $shopInfo->info->logo.'?1';


	$ret .= '<div class="shop-banner"><a href="'.$shopUrl.'"><img class="-logo" src="'.$shopBanner.'" width="100%" /></a><h3>'.$shopInfo->name .'</h3></div>';

	$toolbar = new Toolbar(NULL);
	// Show main navigator
	$ui = new Ui();
	$ui->add('<a class="sg-action" href="'.url('green/shop/'.$shopId.'/plant').'" data-webview="สินค้า"><i class="icon -material">nature_people</i><span>ผลผลิต</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/shop/'.$shopId.'/land').'" data-webview="แปลงผลิต"><i class="icon -material">nature</i><span>แปลงผลิต</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/shop/'.$shopId.'/goods').'" data-webview="สินค้า"><i class="icon -material">local_florist</i><span>สินค้า</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/shop/'.$shopId.'/follow').'" data-webview="ติดตาม"><i class="icon -material">star</i><span>ติดตาม</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/shop/'.$shopId.'/contact').'" data-webview="ติดต่อ"><i class="icon -material">people</i><span>ติดต่อ</span></a>');

	$toolbar->addNav('main', $ui);

	$ret .= $toolbar->build();

	//$ret .= '<nav class="nav -page -app-icon">'.$ui->build().'</nav>'._NL;

	$ret .= R::Page('green.shop.plant', NULL, $shopInfo);


	$ret .= R::Page('green.shop.goods', NULL, $shopInfo);

	$ret .= '<section>';
	$ret .= '<header class="header"><h3>ถาม-ตอบ</h3></header><p>ยังไม่มีรายการ</p></section>';

	$qrCode = SG\qrcode(url('green/shop/'.$shopId),'{width: 512, height: 512}');
	$ret .= '<div class="-info -sg-text-center" style="margin: 16px 0; padding: 16px;">'
		. $shopInfo->info->address.'<br />'
		. 'โทร : '.$shopInfo->info->phone.'<br />'
		. 'แฟกซ์ : '.$shopInfo->info->fax
		. '<div class="ibuy-shop-qrcode -sg-text-center" style="height: 300px;">'
		. '<div style="width:240px; margin: 0 auto;" >'
		. $qrCode
		. '</div>'
		. '</div>'
		. '</div>';


					/*
	if ($productInfo->photo[0]->prop->_url)
		$ret .= '<img class="photo -main" src="'.$productInfo->photo[0]->prop->_url.'" />';
	$ret .= '<h2>'.$productInfo->title.'</h3>';
	$ret .= '<div class="price">'
						. '<span class="price-retail">฿'.number_format($productInfo->info->retailprice,2).'</span>'
						. '<span class="price-list">'.($productInfo->info->listprice != $productInfo->info->retailprice ? '฿'.number_format($productInfo->info->listprice,2) : '').'</span>'
						. '</div>';
						*/

						/*
	$ret .= '<div>';
	foreach ($productInfo->photo as $photo) {
		if ($photo->prop->_url)
			$ret .= '<img class="photo -small" src="'.$photo->prop->_url.'" height="64" />';
	}
	$ret .= '</div>';
	*/

	//$ret .= '<div class="detail">'.sg_text2html($productInfo->info->body).'</div>';






	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '<style type="text/css">
	.photo.-main {width: 100%;}
	.nav.-tab .ui-action {display: flex;}
	.nav.-tab .ui-item>a {padding: 8px; display: block;}
	.price .price-retail {display: block; font-size: 1.2em; color: #f60;}
	.price .price-list {color: #ccc; font-size: 0.8em; text-decoration: line-through;}

	.nav.-app.-page {background-color: white; font-size: 0.9em;}
	.nav.-app.-page>.ui-action {padding:8px;display: flex; justify-content: space-between;}
	.nav.-app.-page .ui-item {text-align: center;}
	.nav.-app.-page .ui-item>a {padding:8px 12px; display: block; border:1px #eee solid; border-radius: 50%; background-color: #f5f5f5;}
	.nav.-app.-page .icon {display: block; margin:0 auto;}
	.qrcode {width: 126px; height: 126px;}

	.ibuy-shop-qrcode .-url {display: block;}
	</style>';

	$ret .= '</section>';

	return $ret;
}
?>