<?php
/**
* Cancel Product
* Created 2019-06-04
* Modify  2019-06-04
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_edit_qrcode($self, $productInfo) {
	if (!$productInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $productInfo->tpid;

	$ret .= '<header class="header -box"><h3>Qr Code</h3></header>';

	$linkUrl = url('ibuy/'.$tpid);
	$qrcodeUrl=_DOMAIN.urlencode($linkUrl);

	$ret .= '<img class="qrcode" src="https://chart.googleapis.com/chart?cht=qr&chl='.$qrcodeUrl.'&chs=160x160&choe=UTF-8&chld=L|2" alt="" style="display: block; margin:0 auto;">';
	$ret .= '<p class="-sg-text-center">'._DOMAIN.$linkUrl.'</p>';


	//$ret .= print_o($productInfo,'$productInfo');

	return $ret;
}
?>