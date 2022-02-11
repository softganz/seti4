<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function green_shop_plant($self, $shopInfo) {
	$shopId = $shopInfo->shopId;

	new Toolbar($self,$shopInfo->name.' @Green Smile', NULL, $shopInfo);

	if (!$shopId) return message('error', 'PROCESS ERROR');

	$ret = '';

	$ret .= '<section>'._NL;
	$ret .= '<header class="header"><h3>ผลผลิต</h3></header>';

	$plantList = R::Model('green.plant.get', '{orgId: '.$shopId.'}', '{debug: false, limit: "*"}');

	$plantUi = new Ui(NULL, 'ui-card green-plant-list');
	$plantUi->addConfig('container', '{tag: "div", class: "green-my-plant"}');

	foreach ($plantList->items as $rs) {
		$isCroped = $rs->cropdate && $rs->cropdate <= date('Y-m-d');
		if ($rs->tagname == 'GREEN,TREE') {
			$linkUrl = url('green/plant/'.$rs->plantid);
		} else if ($rs->tagname == 'GREEN,RUBBER') {
			$linkUrl = url('green/plant/'.$rs->plantid);			
		} else if ($rs->tagname == 'GREEN,PLANT') {
			$linkUrl = url('green/plant/'.$rs->plantid);			
		} else {
			$linkUrl = url('green/plant/'.$rs->plantid);
		}

		$cardStr = '<div class="header"><i class="icon -material">nature</i><h3>'
			. $rs->productname
			. ($rs->landName ? ' <span>@'.$rs->landName : '')
			. ($isCroped ? ' <span>(เก็บเกี่ยวแล้ว)</span>' : '')
			. '</h3></div>'
			. '<div class="detail">'
			. ($rs->startdate ? 'เริ่มลงแปลง '.sg_date($rs->startdate) : '')
			. ($rs->cropdate ? ' วันเก็บเกี่ยว '.sg_date($rs->cropdate) : '')
			. ($rs->tagname == 'GREEN,RUBBER' ? ' จำนวน '.$rs->qty.' ต้น' : '')
			//. print_o($rs,'$rs')
			. '</div>'
			//. '<nav class="nav -more-detail"><a class="btn -link" href="'.$linkUrl.'">รายละเอียดผลผลิต</a></nav>'
			;

		$plantUi->add(
			$cardStr,
			array(
				'id' => 'green-plant-'.$rs->plantid,
				'class' => 'sg-action'.($isCroped ? ' -croped' : ''),
				'href' => $linkUrl,
				'data-rel' => 'box',
				'data-width' => '640',
				'data-height' => '90%',
				'data-webview' => $rs->productname,
			)
		);
	}

	$ret .= $plantUi->build();
	/*

	$cardUi = new Ui('div', 'ui-card ibuy-product-list -full -resv');

	foreach ($plantList->items as $rs) {
		$photoInfo = model::get_photo_property($rs->plantPhotos);

		$headerNav = new Ui();
		$headerNav->addConfig('nav', '{class: "nav -header"}');
		if ($rs->standard) {
			$headerNav->add('<a class="sg-action btn standard -'.str_replace(' ', '',strtolower($rs->standard)).' -'.strtolower($rs->approved).'" href="'.url('green/plant/'.$rs->plantid).'" data-width="640" data-webview="'.htmlspecialchars($rs->productname).'">'.$rs->standard.'</a>');
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
			. '<a class="sg-action btn -link" href="'.url('green/plant/'.$rs->plantid).'" data-width="640" data-webview="'.htmlspecialchars($rs->productname).'"><i class="icon -material -gray">done</i><span>จองผลผลิต</span></a>'
			. '</div>';

		//$cardStr .= print_o($photoInfo);
		$cardUi->add($cardStr);
	}
	$ret .= $cardUi->build();
	*/

	$ret .= '</section><!-- plant -->'._NL;

	//$ret .= print_o($shopInfo, '$shopInfo');
	return $ret;
}
?>