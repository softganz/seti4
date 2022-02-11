<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function ibuy_green_land_qr($self, $landInfo) {
	if (!($landId = $landInfo->landId)) return message('error', 'PROCESS ERROR');

	$ret = '<header class="header -box -hidden">'._HEADER_BACK.'<h3>QR Code แปลงผลิต</h3></header>';

	//R::View('toolbar',$self, $shopInfo->name.' @Green Smile','ibuy.green.my.shop');


	$ret .= '<div class="ibuy-shop-qrcode -sg-text-center" style="height: 300px;"><div style="width="240px; margin: 0 auto;" >'.SG\qrcode(url('ibuy/green/land/'.$landId)).'</div></div>';

	return $ret;
}
?>