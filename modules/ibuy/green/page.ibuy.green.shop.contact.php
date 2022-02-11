<?php
/**
* Green Smile Shop Contact
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function ibuy_green_shop_contact($self, $shopInfo) {
	$shopId = $shopInfo->shopId;

	R::View('toolbar', $self, $shopInfo->name.' @Green Smile','ibuy.green.shop', $shopInfo);

	if (!$shopId) return message('error', 'PROCESS ERROR');

	$ret = '';


	$ret .= '<header class="header"><h3>ติดต่อ</h3></header>';

	$url = _DOMAIN.urlencode(url('ibuy/green/shop/'.$shopId));
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