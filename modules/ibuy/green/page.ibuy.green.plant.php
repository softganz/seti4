<?php
/**
* Green Shop Reservation Plant
*
* @param Object $self
* @param Int $goodsId
* @return String
*/

$debug = true;

function ibuy_green_plant($self, $plantId = NULL) {
	// No goodsId, Show Goods Information
	if ($plantId) return R::Page('ibuy.green.plant.view', $self, $plantId);


	R::View('toolbar',$self,'จองผลผลิต @Green Smile','ibuy.green.shop');

	$ret = '';

	// Show All Goods
	//$ret = '<header class="header"><h3>จองผลผลิต</h3></header>';

	$ret .= '<section>';

	$getItemPerPage = SG\getFirst(post('item'), 10);
	$getPage = post('page');


	//mydb::value('$ORDER$', 'ORDER BY '.$orders[$getOrder][1].' '.(strtoupper($getSort) == 'A' ? 'ASC' : 'DESC'));

	mydb::where('fp.`cropdate` >= :today', ':today', date('Y-m-d'));

	mydb::value('$ORDER$', 'fp.`plantid` DESC');

	if ($getItemPerPage == '*') {
		mydb::value('$LIMIT$', '');
	} else {
		$firstRow = $getPage > 1 ? ($getPage - 1) * $getItemPerPage : 0;
		mydb::value('$LIMIT$', 'LIMIT '.$firstRow.' , '.$getItemPerPage);
	}

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		fp.*
		, l.`landname`
		, o.`name`
		, (SELECT `file` FROM %topic_files% WHERE `orgid` = fp.`orgid` AND `tagname` = "ibuy,plant" AND `refid` = fp.`plantid` ORDER BY `fid` ASC LIMIT 1) `plantPhotos`
		, fp.`qty` - IFNULL((SELECT SUM(`qty`) FROM %ibuy_farmbook% WHERE `plantid` = fp.`plantid`),0) `balance`
		FROM %ibuy_farmplant% fp
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %db_org% o ON o.`orgid` = fp.`orgid`
		%WHERE%
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

	//$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;
	
	$cardUi = new Ui('div', 'ui-card ibuy-product-list -full -resv');

	foreach ($dbs->items as $rs) {
		$photoInfo = model::get_photo_property($rs->plantPhotos);

		$headerNav = new Ui();
		$headerNav->addConfig('nav', '{class: "nav -header"}');
		if ($rs->standard) {
			$headerNav->add('<a class="sg-action btn standard -'.str_replace(' ', '',strtolower($rs->standard)).' -'.strtolower($rs->approved).'" href="'.url('ibuy/green/plant/'.$rs->plantid).'" data-width="640" data-webview="'.htmlspecialchars($rs->productname).'">'.$rs->standard.'</a>');
		}



		$cardStr = '<div class="header"><h5>'.$rs->productname.'</h3><span><b>'.$rs->name.'</b></span></header>'.$headerNav->build().'</div>';

		$cardStr .= '<div class="photo-th">';
		if ($photoInfo->_exists) {
			$cardStr .= '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" />';
		}
		$cardStr .= '</div>';

		$cardStr .= '<div class="detail">'
			. '<p>เริ่มลงแปลง '.($rs->startdate ? sg_date($rs->startdate, 'ว ดด ปปปป') : '').'<br />'
			. 'วันเก็บเกี่ยว '.($rs->cropdate ? sg_date($rs->cropdate, 'ว ดด ปปปป') : '').'<br />'
			. 'ปริมาณผลผลิต '.$rs->qty.' '.$rs->unit
			. '</p>'
			. '<p>คงเหลือ <b>'.$rs->balance.'</b> '.$rs->unit.'</p>'
			//. nl2br($rs->detail)
			. '</div>';

		$cardStr .= '<div class="ibuy-price-label ibuy-product-available">'
			. '<a class="sg-action btn -link" href="'.url('ibuy/green/plant/'.$rs->plantid).'" data-width="640" data-webview="'.htmlspecialchars($rs->productname).'"><i class="icon -material -gray">done</i><span>จองผลผลิต</span></a>'
			. '</div>';

		//$cardStr .= print_o($photoInfo);
		$cardUi->add($cardStr);
	}
	$ret .= $cardUi->build();

	///$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;

	//$ret .= print_o($dbs, '$dbs');

	$ret .= '</section>';
	return $ret;
}
?>