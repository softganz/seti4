<?php
/**
* Green :: QR Code Name Card
* Created 2021-01-16
* Modify  2021-01-16
*
* @param Object $self
* @return String
*/

$debug = true;

function green_namecard($self) {
	$ret = '';

	$qrCodeWebApp = SG\qrcode('green','{width: 512, height: 512, domain: "https://communeinfo.com/", imgWidth: "128px", imgHeight: "128px"}');

	$logoImage = 'https://communeinfo.com/themes/default/logo-green.png';

	$header = '<img class="-logo" src="'.$logoImage.'" height="64" />'
		. '<div class="-address"><b>Plateform : Green Smile</b><br />Green Smile Application</div>';

	$qrCodePlayStore = SG\qrcode('store/apps/details?id=com.softganz.green','{width: 512, height: 512, domain: "https://play.google.com/", imgWidth: "128px", imgHeight: "128px"}');


	$appStr = '<div class="header -sg-flex">'
		. $header
		. '</div>'
		. '<div class="detail -sg-flex">'
		. '<p class="">เว็บแอพพลิเคชั่น<br /><b>สำหรับ iPhone / Notebook</b><br />www.ข้อมูลชุมชน.com</p>'
		. '<div class="qrcode">'
		. $qrCodeWebApp
		. '</div>'
		. '</div>';

	$playStr = '<div class="header -sg-flex">'
		. $header
		. '</div>'
		. '<div class="detail -sg-flex">'
		. '<p class="">ดาวน์โหลดแอพพลิเคชั่น<br /><b>สำหรับ Android Phone</b><br />Google Play Store</p>'
		. '<div class="qrcode">'
		. $qrCodePlayStore
		. '</div>'
		. '</div>';

	$qrCard = new Ui(NULL, 'ui-card -sg-flex imed-namecard');

	for ($i=1; $i <=5 ; $i++) { 
		$qrCard->add(
			$appStr,
			'{style: "margin: 0 0 16px 0;"}'
		);

		$qrCard->add($playStr,
			'{style: "margin: 0 0 16px 0;"}'
		);

	}

	$ret .= $qrCard->build();

	$ret .= '<style type="text/css">
	.imed-namecard>.ui-item {width: 48%; border: 1px #000 solid; position: relative;}
	.imed-namecard .-logo {display: block; margin: 4px; height: 40px;}
	.imed-namecard .-qrcode {width: 100px; height: 100px; display: block; margin: 0 8px 80x 0;}
	.imed-namecard .ui-item p {flex: 1; margin: 0; padding: 0; text-align: center;}
	.imed-namecard .ui-item .qrcode {flex: 0; margin: 0; padding: 0;}
	.imed-namecard .ui-item .-url {position: absolute; left: 16px; text-align: left; bottom: 8px; font-size: 0.65em;}
	.imed-namecard .-address {flex: 1; text-align: right; font-size: 0.8em;}
	.imed-namecard>.ui-item>.detail {padding: 0px 16px 16px;}
	</style>';
	return $ret;
}
?>