<?php
/**
* iBuy :: Green Smile Home
* Created 2018-06-15
* Modify  2020-06-23
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_green($self) {
	//$ret.='<div class="banner-top"><a href="https://communeinfo.com/paper/320" style="margin: 0; display: block;"><img src="https://communeinfo.com/upload/banner-green.png" width="100%" /></a></div>';

	$ret .= R::Page('ibuy.green.app', $self);

	/*
	$qrCode = SG\qrcode('?id=com.softganz.green','{width: 512, height: 512, domain: "https://play.google.com/store/apps/details", imgWidth: "200px", imgHeight: "200px"}');
	$ret .= '<div class="-info -sg-text-center" style="margin: 16px auto; padding: 16px;">'
		. $qrCode.'<br />'
		. '<a class="btn -link" href="https://play.google.com/store/apps/details?id=com.softganz.green" target="_blank">ดาวน์โหลดแอปกรีนสมาย - Green Smile</a><br />'
		. '</div>';
	*/
	return $ret;
}
?>