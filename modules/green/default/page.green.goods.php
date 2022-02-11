<?php
/**
* GoGreen Spp Shop Goods
*
* @param Object $self
* @param Int $goodsId
* @return String
*/

$debug = true;

function green_goods($self, $goodsId = NULL) {
	// No goodsId, Show Goods Information
	if ($goodsId) return R::Page('green.goods.view', $self, $goodsId);



	$ret = '';

	// Show All Goods
	$ret = '<header class="header"><h3>สินค้า</h3></header>';

	$getItemPerPage = SG\getFirst(post('item'), 12);
	$getPage = post('page');


	//mydb::value('$ORDER$', 'ORDER BY '.$orders[$getOrder][1].' '.(strtoupper($getSort) == 'A' ? 'ASC' : 'DESC'));

	mydb::where('p.`outofsale` IN ("N","O") AND p.`listprice` > 0');

	mydb::value('$ORDER$', 't.`tpid` DESC');

	if ($getItemPerPage == '*') {
		mydb::value('$LIMIT$', '');
	} else {
		$firstRow = $getPage > 1 ? ($getPage - 1) * $getItemPerPage : 0;
		mydb::value('$LIMIT$', 'LIMIT '.$firstRow.' , '.$getItemPerPage);
	}

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		t.`tpid`, t.`title`
		, t.`orgid`
		, p.*
		, ph.`file` photo
		, t.`view`
		FROM %ibuy_product% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_files% ph ON ph.`tpid` = t.`tpid` AND ph.`fid`
		%WHERE%
		GROUP BY t.`tpid`
		ORDER BY $ORDER$
		$LIMIT$
		;';

	$dbs = mydb::select($stmt);


	$pagePara['q'] = $getSearch;
	$pagePara['order'] = $getOrder;
	$pagePara['sort'] = $getSort;
	$pagePara['item'] = $getItemPerPage != 100 ? $getItemPerPage : NULL;
	$pagePara['page'] = $getPage;
	$pageNav = new PageNavigator($getItemPerPage, $getPage, $dbs->_found_rows, q(), false, $pagePara);
	$itemNo = $pageNav ? $pageNav->FirstItem() : 0;

	$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;
	
	$cardUi = new Ui(NULL, 'ui-card ibuy-product-list -full -goods');

	foreach ($dbs->items as $rs) {
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

		$cardUi->add(
			$cardStr,
			array(
				'class' => 'sg-action',
				'href' => url('green/goods/'.$rs->tpid),
				'data-webview' => htmlspecialchars($rs->title),
				'onclick' => '',
			)
		);


		/*
		$url = '<a class="sg-action" href="'.url('green/goods/'.$rs->tpid).'" title="'.htmlspecialchars($rs->title).'" data-webview=true data-webview-title="'.$rs->title.'">';
		$cardStr = '<div class="photo">'.$url;
		if ($rs->photo) {
			$photo = model::get_photo_property($rs->photo);
			$cardStr .= '<img class="photo" src="'.$photo->_url.'" alt="'.htmlspecialchars($rs->title).'" />';
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

	$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;

	//$ret .= print_o($productInfo, '$productInfo');
	return $ret;
}
?>