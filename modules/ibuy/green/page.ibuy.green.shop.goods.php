<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function ibuy_green_shop_goods($self, $shopInfo) {
	$shopId = $shopInfo->shopId;

	R::View('toolbar', $self, $shopInfo->name.' @Green Smile','ibuy.green.shop', $shopInfo);

	if (!$shopId) return message('error', 'PROCESS ERROR');

	$ret = '';

	$ret .= '<section class="">';
	$ret .= '<header class="header"><h3>สินค้า</h3></header>';

	mydb::where('t.`type`="ibuy" AND `outofsale` IN ("N","O")');
	mydb::where('t.`orgid` = :shopid', ':shopid', $shopId);

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

	$cardUi = new Ui(NULL, 'ui-card ibuy-product-list -full');

	foreach ($productList->items as $rs) {

		$cardStr = R::View(
			'ibuy.render.card',
			$rs,
			array(
				'showSaleLabel' => false,
				'link' => (Object) array(
					'href'=>url('ibuy/green/goods/'.$rs->tpid),
					'class' => 'sg-action',
					'data-webview' => htmlspecialchars($rs->title),
					'title' => htmlspecialchars($rs->title),
					'onclick' => '',
				),
			)
		);

		$cardUi->add($cardStr);

		/*
		$url = '<a class="sg-action" href="'.url('ibuy/green/goods/'.$rs->tpid).'" title="'.htmlspecialchars($rs->title).'" data-webview=true data-webview-title="'.$rs->title.'">';
		$cardStr = '<div class="photo">'.$url;
		if ($rs->photo) {
			$photo = model::get_photo_property($rs->photo);
			$cardStr .= '<img class="" src="'.$photo->_url.'" alt="'.htmlspecialchars($rs->title).'" />';
		} else {
			$cardStr .= '<img class="nophoto" src="/library/img/none.gif" alt="" />'._NL;
		}
		$cardStr .= '</a></div>'._NL;
		$cardStr .= '<h3>'.$url.$rs->title.'</a></h3>'._NL;
		$cardStr .= '<div class="price">'
							. '<span class="price-retail">฿'.number_format($rs->retailprice,2).'</span>'
							. '<span class="price-list">'.($rs->listprice != $rs->retailprice ? '฿'.number_format($rs->listprice,2) : '').'</span>'
							. '</div>';
		//$cardStr .= '<div class="summary"><p>'.$rs->title.'</p><p><a href="'.url('ibuy/'.$rs->tpid).'">'.tr('Details').'</a></p></div>'._NL;
		// Create product price and sale label
		//$ret .= R::View('ibuy.price.label',$rs)._NL;
		//$ret .= R::View('ibuy.sale.label',$rs,NULL,true)._NL;

		$cardUi->add($cardStr);
		*/
	}

	$ret .= $cardUi->build();


	$ret .= '</section>';

	//$ret .= print_o($shopInfo,'$shopInfo');
	return $ret;
}
?>