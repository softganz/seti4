<?php
/**
* Project detail
*
* @param Object $self
* @param Object $psnInfo
* @param Object $para
* @return String
*/
function view_flood_climate_nav($psnInfo,$options) {
	$submenu = q(2);

	$ret = '';

	$isAdmin = $psnInfo->RIGHT & IS_ADMIN;
	$isRight = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & IS_EDITABLE;


	$ui = new Ui(NULL,'ui-nav -main');

	$ui->add('<a class="" href="'.url('flood/climate').'"><i class="icon -material">pool</i><span class="-hidden">สถานการณ์</span></a>');
	$ui->add('<a class="" href="'.url('flood/climate/cctv').'" data-webview="CCTV"><i class="icon -material">videocam</i><span class="-hidden">CCTV</span></a>');
	$ui->add('<a class="" href="'.url('flood/climate/map').'" data-webview="แผนที่"><i class="icon -material">person_pin</i><span class="-hidden">Map</span></a>');
	$ui->add('<a class="" href="'.url('flood/climate/network').'" data-webview="Networks"><i class="icon -material">group</i><span class="-hidden">Networks</span></a>');
	$ui->add('<a class="" href="'.url('flood/climate/menu').'"><i class="icon -material">menu</i><span class="-hidden">Menu</span></a>');

	$ret .= $ui->build();

	return $ret;
}
?>