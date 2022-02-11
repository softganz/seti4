<?php
/**
* Green : App Report
* Created 2020-09-20
* Modify  2020-09-20
*
* @param Object $self
* @return String
*
* @usage green/app/shoping
*/

$debug = true;

function green_app_report($self) {

	$isAdmin = is_admin('green');

	$ret = '<header class="header"><h3>Report</h3></header>';

	$ret .= R::Page('green.report', NULL);
	
	// Show App QR Code at Play Store
	$qrCode = SG\qrcode('?id=com.softganz.green','{width: 512, height: 512, domain: "https://play.google.com/store/apps/details", imgWidth: "200px", imgHeight: "200px"}');


	$ret .= '<div class="qrcode" style="margin: 16px 8px; padding: 16px; background-color: #fff; border-radius: 8px;">'
		. $qrCode.'<br />'
		. '<a class="btn -link" href="https://play.google.com/store/apps/details?id=com.softganz.green" target="_blank">ดาวน์โหลดแอปกรีนสมาย - Green Smile</a><br />'
		. '</div>';

	return $ret;
}
?>