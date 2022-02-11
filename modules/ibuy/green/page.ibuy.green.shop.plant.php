<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function ibuy_green_shop_plant($self, $shopInfo) {
	$shopId = $shopInfo->shopId;

	R::View('toolbar',$self,$shopInfo->name.' @Green Smile','ibuy.green.shop', $shopInfo);

	if (!$shopId) return message('error', 'PROCESS ERROR');

	$ret = '';

	$ret .= '<section>'._NL;
	$ret .= '<header class="header"><h3>ผลผลิต</h3></header>';

	$plantList = R::Model('ibuy.plant.get', '{shopId: '.$shopId.'}', '{limit: "*"}');


	$cardUi = new Ui('div', 'ui-card ibuy-product-list -full -resv');

	foreach ($plantList->items as $rs) {
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

	$ret .= '</section><!-- plant -->'._NL;

	//$ret .= print_o($shopInfo, '$shopInfo');
	return $ret;
}
?>