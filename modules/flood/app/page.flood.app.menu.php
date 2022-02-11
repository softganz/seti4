<?php
/**
* Flood Application Menu
*
* @param Object $self
* @return String
*/

$debug = true;

function flood_app_menu($self) {
	$ui = new Ui(NULL, 'ui-menu flood-app-menu');

	$ui->add('<a class="sg-action" href="{url:news}" data-webview="สถานการณ์ลุ่มน้ำ" data-webview-history="true"><i class="icon -star"></i><span>สถานการณ์ลุ่มน้ำ</span></a>');
	$ui->add('<a class="sg-action" href="http://www.songkhla.tmd.go.th/RF/Monitor/" data-webview="น้ำฝนออนไลน์"><i class="icon -star"></i><span>รายงานออนไลน์ปริมาณน้ำฝนในลุ่มน้ำคลองอู่ตะเภา</span></a>');
	$ui->add('<a class="sg-action" href="http://www.songkhla.tmd.go.th/index.php?modules=forecast" data-webview="พยากรณ์อากาศ"><i class="icon -star"></i><span>พยากรณ์อากาศภาคใต้ฝั่งตะวันออก</span></a>');

	$ui->add('<a class="sg-action" href="http://www.songkhla.tmd.go.th/RadarSat/radar/stp_last_bkk.html" data-webview="เรดาห์"><i class="icon -star"></i><span>ภาพเรดาห์ฯ สทิงพระ ล่าสุด</span></a>');

	$ui->add('<a class="sg-action" href="http://www.songkhla.tmd.go.th/RadarSat/radar/stp_loop_bkk.html" data-webview="เรดาห์"><i class="icon -star"></i><span>ภาพเรดาห์ฯ สทิงพระ เคลื่อนไหว</span></a>');

	$ui->add('<a class="sg-action" href="{url:flood/app/cam/20}" data-rel="none" data-webview="true" data-webview="แผนที่อากาศ"><i class="icon -star"></i><span>แผนที่อากาศ</span></a>');

	if (i()->ok) {
		$ui->add('<a class="sg-action" href="'.url('profile/'.i()->uid).'" data-webview="'.i()->name.'"><img src="'.model::user_photo(i()->username).'" width="24" height="24" style="vertical-align: middle; display: inline-block; margin-right: 4px;" /><span>My Account</span></a>');
		$ui->add('<a class="" href="'.url('signout',array('ret_url'=>url('flood/app/menu'))).'"><i class="icon -unlock"></i><span>Sign Out</span></a>');
	}
	else
		$ui->add('<a href="'.url('flood/app/signin',array('action'=>url('flood/app/menu'))).'"><i class="icon -signin"></i><span>Sign In</span></a>');

	$ret .= $ui->build();

	$ret .= '<style type="text/css">
	.flood-app-menu {padding: 16px 0;}
	.flood-app-menu>.ui-item>a {padding: 8px;}
	</style>';
	return $ret;
}
?>