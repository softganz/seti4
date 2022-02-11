<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $orgInfo
* @return String
*/

$debug = true;

function green_my_goods($self) {
	$orgInfo = R::Model('green.shop.get', 'my', '{debug: false}');
	$orgId = $orgInfo->orgId;

	$orgSelectCard = R::View('green.my.select.org', '{"href": "'.url('green/organic/my/org/$id').'", "data-rel": "none", "data-done": "close | reload:'.url('green/my/goods').'"}');

	if (!($orgId = $orgInfo->shopId)) return '<header class="header"><h3>เลือกกลุ่มสำหรับจัดการข้อมูล?</h3></header>'.$orgSelectCard->build();

	$getShow = post('show');

	$isAdmin = is_admin('green');
	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;
	$isAddProduct = $isEdit || in_array($orgInfo->is->membership,array('NETWORK'));

	$ret = '';
	//$ret .= print_o($orgInfo);

	$toolbar = new Toolbar($self, 'สินค้า @'.$orgInfo->name);
	$toolbarNav = new Ui(NULL, 'ui-nav -main');

	$toolbarNav->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	//$toolbarNav->add('<a class="sg-action" href="'.url('green/my/goods', array('show' => 'all')).'" data-rel="#green-my-goods"><i class="icon -material">view_list</i><span>ทั้งหมด</span></a>');
	$toolbarNav->add('<a class="sg-action" href="'.url('green/my/goods').'" data-rel="#green-my-goods"><i class="icon -material">check_circle</i><span>มีของ</span></a>');
	$toolbarNav->add('<a class="sg-action" href="'.url('green/my/goods', array('show' => 'out')).'" data-rel="#green-my-goods"><i class="icon -material">cancel</i><span>ของหมด</span></a>');
	$toolbarNav->add('<a class="sg-action" href="'.url('green/my/goods', array('show' => 'no')).'" data-rel="#green-my-goods"><i class="icon -material">visibility_off</i><span>เลิกขาย</span></a>');
	$toolbarNav->add('<a class="sg-action -add" href="'.url('green/my/goods/form').'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่ม</span></a>');

	if ($isAddLand) {
		$toolbarNav->add('<a class="sg-action -add" href="#green-land-form" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');
	}
	$toolbar->addNav('main', $toolbarNav);

	//$ret .= '<header class="header"><h3>สินค้า</h3></header>';

	$ret .= '<section id="green-my-goods" data-url="'.url('green/my/goods').'">';

	if ($getShow == 'no') {
		mydb::where('p.`outofsale` IN ("Y")');
	} else if ($getShow == 'out') {
		mydb::where('p.`outofsale` IN ("O")');
	} else if ($getShow == 'all') {
	} else {
		mydb::where('p.`outofsale` IN ("N","O")');
	}
	mydb::where('t.`orgid` = :shopid', ':shopid', $orgId);

	mydb::value('$ORDER', 'p.`tpid` DESC');

	$stmt = 'SELECT
		t.`tpid`, t.`title`
		, t.`orgid`
		, p.*
		, (SELECT `file` FROM %topic_files% WHERE `tpid` = t.`tpid` ORDER BY `fid` ASC LIMIT 1) `photo`
		, t.`view`
		, LEFT(r.`body`,100) `detail`
		FROM %ibuy_product% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_revisions% r USING(`tpid`)
		%WHERE%
		GROUP BY t.`tpid`
		ORDER BY $ORDER
		;';

	$productDbs = mydb::select($stmt);
	//$ret .= print_o($productDbs,'$productDbs');

	$cardUi = new Ui('div', 'ui-card green-my-product-list -full');

	foreach ($productDbs->items as $rs) {
		$isEditItem = $isEdit || $rs->uid == i()->uid;

		$headerNav = new Ui();
		$headerNav->addConfig('nav', '{class: "nav -header"}');
		$headerNav->add('<a class="sg-action btn -link" href="'.url('green/goods/'.$rs->tpid).'" data-webview="'.htmlspecialchars($rs->title).'"><i class="icon -material">find_in_page</i></a>');
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
		$url = '<a class="sg-action" href="'.url('green/goods/'.$rs->tpid).'" title="'.htmlspecialchars($rs->title).'" data-webview=true data-webview-title="'.$rs->title.'">';
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

	$ret .= '<div class="template -hidden">'
		. '<div id="green-org-select"><header class="header">'._HEADER_BACK.'<h3>เลือกกลุ่ม</h3></header>'.$orgSelectCard->build().'</div>'
		. '</div>';

	$ret .= '</section>';

	$ret .= '<style type="text/css">
	.inline-edit-field {min-width: 60px;}
	</style>';
	return $ret;
}
?>