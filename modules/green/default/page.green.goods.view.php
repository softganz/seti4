<?php
/**
* Module Method
*
* @param Int $tpid
* @return String
*/

$debug = true;

function green_goods_view($self, $tpid) {
	$productInfo = R::Model('ibuy.product.get', $tpid, '{debug: false}');

	$ret .= '';
	
	if ($productInfo->orgid) {
		$ret = '<h3><a class="sg-action btn -link" href="'.url('green/shop/'.$productInfo->orgid).'" data-webview="'.$productInfo->info->shopName.'">'.$productInfo->info->shopName.'</a></h3>';

	}

	$ui = new Ui();
	$ui->add('<a href="">ภาพรวม</a>');
	$ui->add('<a href="">รายละเอียดสินค้า</a>');
	$ui->add('<a href="">คะแนน</a>');
	$ui->add('<a href="">ถาม-ตอบ</a>');
	//$ret .= '<nav class="nav -tab">'.$ui->build().'</nav>';

	$photo = reset($productInfo->photos);
	if ($photo->_url)
		$ret .= '<img class="photo -main" src="'.$photo->_url.'" />';
	$ret .= '<h2>'.$productInfo->title.'</h3>';
	$ret .= '<div class="price">'
		. '<span class="price-retail">฿'.number_format($productInfo->info->price1,2).'</span>'
		. '<span class="price-list">'.($productInfo->info->listprice != $productInfo->info->retailprice ? '฿'.number_format($productInfo->info->listprice,2) : '').'</span>'
		. '</div>';

	$ret .= '<div>';
	foreach ($productInfo->photo as $photo) {
		if ($photo->prop->_url)
			$ret .= '<img class="photo -small" src="'.$photo->prop->_url.'" height="64" />';
	}
	$ret .= '</div>';

	//$ret .= print_o($productInfo,'$productInfo');

	$ret .= '<div class="detail">'.sg_text2html($productInfo->info->body).'</div>';

	$shopInfo = R::Model('green.shop.get', $productInfo->orgid);
	$ret .= '<div class="shop-info" style="margin: 16px 0; background-color: #fff; padding: 16px;">'
		. '<h3>ติดต่อ-สอบถาม</h3><h4>'.$productInfo->info->shopName.'</h4>'
		. '<address>'.$shopInfo->info->address.'</address>'
		. '<div>โทร. '.$shopInfo->info->phone.'</div>'
		//. print_o($shopInfo,'$shopInfo')
		. '</div>';

	$ret .= '<header class="header"><h5>ถาม-ตอบ</h5></header>';

	if ($productInfo->orgid) {
		mydb::where('t.`tpid` != :tpid AND t.`type`="ibuy" AND `outofsale` IN ("N","O")', ':tpid', $tpid);
		mydb::where('t.`orgid` = :shopid', ':shopid', $productInfo->orgid);

		mydb::value('$ORDER', 't.`tpid` DESC');

		$stmt = 'SELECT
			t.`tpid`, t.`title`
			, t.`orgid`
			, p.*
			, ph.`file` photo
			, t.`view`
			FROM %topic% t
				LEFT JOIN %ibuy_product% p ON p.`tpid` = t.`tpid`
				LEFT JOIN %topic_files% ph ON ph.`tpid` = t.`tpid` AND ph.`fid`
			%WHERE%
			GROUP BY t.`tpid`
			ORDER BY $ORDER
			;';

		$productList = mydb::select($stmt);
		//$ret .= print_o($productList);

		$cardUi = new Ui(NULL, 'ui-card ibuy-product-list ibuy-product-list-full');

		foreach ($productList->items as $rs) {

			$cardStr = R::View(
				'green.goods.render.card',
				$rs,
				array(
					'showSaleLabel' => false,
					'link' => (Object) array(
						'href'=>url('green/goods/'.$rs->tpid),
						'class' => 'sg-action',
						'data-webview' => htmlspecialchars($rs->title),
						'title' => htmlspecialchars($rs->title),
						'onclick' => '',
					),
				)
			);

			$cardUi->add($cardStr);
		}

		if ($cardUi->count()) {

			$ret .= '<section class="" style="margin: 32px 0;">';
			$ret .= '<heder class="header"><h3><a class="sg-action btn -link" href="'.url('green/shop/'.$productInfo->orgid).'" data-webview="'.$productInfo->info->shopName.'">สินค้าจากร้าน '.$productInfo->info->shopName.'</a></h3></header>';

			$ret .= $cardUi->build();
			$ret .= '</section>';
		}
	}

	$ret .= '<div><h3>สินค้าหมวดเดียวกัน</h3></div>';



	//$ret .= print_o($productInfo, '$productInfo');

	$ret .= '<style type="text/css">
	.photo.-main {width: 100%;}
	.nav.-tab .ui-action {display: flex;}
	.nav.-tab .ui-item>a {padding: 8px; display: block;}
	.price .price-retail {display: block; font-size: 1.2em; color: #f60;}
	.price .price-list {color: #ccc; font-size: 0.8em; text-decoration: line-through;}

	</style>';
	return $ret;
}
?>