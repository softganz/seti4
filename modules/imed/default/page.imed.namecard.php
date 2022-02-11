<?php
/**
* iMed :: QR Code Name Card
* Created 2020-07-08
* Modify  2020-07-08
*
* @param Object $self
* @return String
*/

$debug = true;

function imed_namecard($self) {
	$ret = '';

	$qrCodeWebApp = SG\qrcode('imed/m','{width: 512, height: 512, domain: "https://communeinfo.com/", imgWidth: "128px", imgHeight: "128px"}');

	$address = '<div class="-address">มูลนิธิชุมชนสงขลา<br />73 ซ.5 ถ.เพชรเกษม ต.หาดใหญ่ จ.สงขลา 90110<br />โทรศัพท์ 074-221286 , 086-4892086</div>';

	$qrCodePlayStore = SG\qrcode('store/apps/details?id=com.softganz.imedhome','{width: 512, height: 512, domain: "https://play.google.com/", imgWidth: "128px", imgHeight: "128px"}');

	$appStr = '<div class="header -sg-flex">'
		. '<img class="-logo" src="https://communeinfo.com/themes/default/homemed-big.png" height="64" />'
		. $address
		. '</div>'
		. '<div class="detail -sg-flex">'
		. '<p class="">เว็บแอพพลิเคชั่น</p>'
		. '<div class="qrcode">'
		. $qrCodeWebApp
		. '</div>'
		. '</div>';

	$playStr = '<div class="header -sg-flex">'
		. '<img class="-logo" src="https://communeinfo.com/themes/default/homemed-big.png" height="64" />'
		. $address
		. '</div>'
		. '<div class="detail -sg-flex">'
		. '<p class="">ดาวน์โหลดแอพพลิเคชั่น<br />Google Play Store</p>'
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
	.imed-namecard .-logo {display: block; margin: 4px; height: 48px;}
	.imed-namecard .-qrcode {width: 100px; height: 100px; display: block; margin: 0 8px 80x 0;}
	.imed-namecard .ui-item p {flex: 1; margin: 0; padding: 0; text-align: center;}
	.imed-namecard .ui-item .qrcode {flex: 0; margin: 0; padding: 0;}
	.imed-namecard .ui-item .-url {position: absolute; left: 16px; text-align: left; bottom: 8px; font-size: 0.7em;}
	.imed-namecard .-address {flex: 1; text-align: right; font-size: 0.6em;}
	</style>';
	return $ret;
}
?>