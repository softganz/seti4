<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function ibuy_green_my_goods($self) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopid : location('ibuy/green/my/shop');

	$getShow = post('show');

	$isAdmin = user_access('administer ibuys');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	$isAddProduct = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	$ret = '';

	R::View('toolbar',$self, $shopInfo->name.' @Green Smile','ibuy.green.my.shop');

	$headerNav = new Ui();
	$headerNav->addConfig('nav', '{class: "nav -page -sg-text-right"}');

	$headerNav->add('<a class="btn -link" href="'.url('ibuy/green/my/goods', array('show' => 'all')).'">ALL</a>');
	$headerNav->add('<a class="btn -link" href="'.url('ibuy/green/my/goods', array('show' => 'out')).'">OUT</a>');
	$headerNav->add('<a class="btn -link" href="'.url('ibuy/green/my/goods', array('show' => 'no')).'">NO</a>');
	$headerNav->add('<a class="sg-action btn -primary" href="'.url('ibuy/my/goods/form').'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่มสินค้า</span></a>');

	$ret .= $headerNav->build();

	//$ret .= '<header class="header"><h3>สินค้า</h3></header>';

	$ret .= '<section class="">';

	if ($getShow == 'no') {
		mydb::where('t.`type`="ibuy" AND `outofsale` IN ("Y")');
	} else if ($getShow == 'out') {
		mydb::where('t.`type`="ibuy" AND `outofsale` IN ("O")');
	} else if ($getShow == 'all') {
	} else {
		mydb::where('t.`type`="ibuy" AND `outofsale` IN ("N","O")');
	}
	mydb::where('t.`orgid` = :shopid', ':shopid', $shopId);

	mydb::value('$ORDER', 't.`tpid` DESC');

	$stmt = 'SELECT
		t.`tpid`, t.`title`
		, t.`orgid`
		, p.*
		, (SELECT `file` FROM %topic_files% WHERE `tpid` = t.`tpid` ORDER BY `fid` ASC LIMIT 1) `photo`
		, t.`view`
		, LEFT(r.`body`,100) `detail`
		FROM %topic% t
			LEFT JOIN %ibuy_product% p USING(`tpid`)
			LEFT JOIN %topic_revisions% r USING(`tpid`)
		%WHERE%
		GROUP BY t.`tpid`
		ORDER BY $ORDER
		;';

	$productDbs = mydb::select($stmt);

	$cardUi = new Ui('div', 'ui-card ibuy-my-product-list -full');

	foreach ($productDbs->items as $rs) {
		$isEditItem = $isEdit || $rs->uid == i()->uid;

		$headerNav = new Ui();
		$headerNav->addConfig('nav', '{class: "nav -header"}');
		$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/green/goods/'.$rs->tpid).'" data-webview="'.htmlspecialchars($rs->title).'"><i class="icon -material">find_in_page</i></a>');
		if ($isEditItem) $headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/'.$rs->tpid.'/info.manage').'" data-rel="box" data-width="640" data-webview="'.htmlspecialchars($rs->title).'"><i class="icon -material">edit</i></a>');

		$cardStr = '<div class="header">'
			. '<h3>'.$rs->title.'</h3>'
			. $headerNav->build()
			. '</div>'
			;

		$cardStr .= '<div class="detail -sg-flex" style="padding: 0 1px;">';
		if ($rs->photo) {
			$photoInfo = model::get_photo_property($rs->photo);
			$cardStr .= '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" height="40" />';
		}

		if ($rs->detail) $cardStr .= '<p style="flex: 1;">'.strip_tags($rs->detail).'</p>';

		$tables = new Table();
		$tables->addClass('-center');
		$tables->addClass('ibuy-price-table -admin');
		foreach (cfg('ibuy.price.use') as $key => $item) {
			$tables->thead['money -'.$key] = $item->label;
			$tables->rows[0][] = number_format($rs->{$key},2);
		}

		$cardStr .= $tables->build();

		$cardStr .= '</div><!-- detail -->';

		$cardUi->add($cardStr, '{class: "-status-'.$rs->outofsale.'"}');

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

	$ret .= '<style type="text/css">
	.inline-edit-field {min-width: 60px;}
	</style>';
	return $ret;
}
?>