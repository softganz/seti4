<?php
/**
* Project :: QR Code Name Card
* Created 2021-01-24
* Modify  2021-01-24
*
* @param Object $self
* @return String
*/

$debug = true;

function project_namecard($self) {
	$ret = '';

	$qrCodeWebApp = SG\qrcode('','{width: 512, height: 512, domain: "https://1t1u.psu.ac.th/", imgWidth: "128px", imgHeight: "128px"}');

	$logoImage = 'https://ppi.psu.ac.th/1t1u/webicon.png';

	$header = '<img class="-logo" src="'.$logoImage.'" height="64" />'
		. '<div class="-address"><b>1 Tambon 1 University : 1 ตำบล 1 มหาวิทยาลัย</b><br />1T1U Application</div>';

	$qrCodePlayStore = SG\qrcode('store/apps/details?id=com.softganz.otou','{width: 512, height: 512, domain: "https://play.google.com/", imgWidth: "128px", imgHeight: "128px"}');


	$appStr = '<div class="header -sg-flex">'
		. $header
		. '</div>'
		. '<div class="detail -sg-flex">'
		. '<p class="">เว็บแอพพลิเคชั่น<br /><b>สำหรับ iPhone / Notebook</b><br />1t1u.psu.ac.th</p>'
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