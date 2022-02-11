<?php
/**
* Green Smile Shop Follow
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function green_shop_follow($self, $shopInfo) {
	$shopId = $shopInfo->shopId;

	new Toolbar($self,$shopInfo->name.' @Green Smile', NULL, $shopInfo);

	if (!$shopId) return message('error', 'PROCESS ERROR');

	$ret = '';

	$ret .= '<header class="header"><h3>ติดตาม</h3></header>';


	$url = _DOMAIN.urlencode(url('green/shop/'.$shopId));
	$qrCode = '<img class="qrcode" src="https://chart.googleapis.com/chart?cht=qr&chl='.$url.'&chs=160x160&choe=UTF-8&chld=L|2" alt=""><br />'.urldecode($url);

	$ret .= '<div class="-info -sg-text-center" style="margin: 16px 0; padding: 16px;">'
		. $shopInfo->info->address.'<br />'
		. 'โทร : '.$shopInfo->info->phone.'<br />'
		. 'แฟกซ์ : '.$shopInfo->info->fax
		. '<div>'.$qrCode.'</div>'
		.'</div>';

	return $ret;
}
?>