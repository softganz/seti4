<?php
/**
* Flood City Climate - Menu
* Created 2019-08-22
* Modify  2019-08-22
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_climate_menu($self) {
	R::View('toolbar', $self, 'City Climate', 'flood.climate');

	$isAdmin = is_admin();
	$isAccessDev = in_array(i()->username, array('softganz','momo'));
	$isLocalHost = _DOMAIN_SHORT == 'localhost';


	$ui = new Ui();

	//$ret .= '<header class="header"><h3>'.i()->name.'</h3><nav class="nav">'.$ui->build().'</nav></header>';
	if (!i()->ok) {
		if (post('signform')) {
			$ret .= '<header class="header -box"><h3>Sign In</h3></header>';
			$ret .= R::View('signform', '{time:-1}');
			$ret .= '<style type="text/css">
			.toolbar.-main.-imed h2 {text-align: center;}
			.form.signform .form-item {margin-bottom: 16px; position: relative;}
			.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
			.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
			.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
			.login {width: 100%; margin: 0; padding: 0; border: none; background-color: transparent;}
			.login.-normal h3 {display: none;}
			.login .-form {width: auto; float: none; margin: 0; padding: 16px 32px;}
			#cboxLoadedContent .form {padding: 0;}
			.form-item.-edit-cookielength {display: none;}
			.form.signform .ui-action>a {display: block;}
			.login .-info {display: none;}
			.login .-form>h5, .login .-form>ul {display: none;}
			</style>';
			return $ret;
		} else {
			$ret .= '<a class="sg-action btn -primary" href="'.url('flood/climate/menu', array('signform'=>'yes')).'" data-rel="box" data-width="320" style="margin: 32px; padding: 16px 0; display: block;"><i class="icon -material">lock</i><span>เข้าสู่ระบบสมาชิก</span></a>';
		}
	} else {

		$ret .= '<div class="imed-patient-photo-wrapper" style="margin:0 0 16px; padding: 16px 0; position:relative; background-color: #fff">';
		$ret .= '<div id="imed-patient-photo" style="width: 196px; height: 196px; margin: 0px auto 32px; display: block; border-radius: 50%; overflow: hidden; border: 2px #eee solid;"><img src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a></div>';

		$ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('my/api/photo.change').'" data-rel="notify" data-done="load:#main:'.url('imed/app/menu').'" style="position: absolute; top: 16px; right: calc(50% - 112px); padding: 0; margin: 0; background-color: transparent;"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" x-capture="capture" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>';

		$ret .= '</div>';
	}



	//$ret .= '<img src="'.model::user_photo(i()->username).'" width="200" height="200" style="display: block; margin: 32px auto 32px; border: 2px #fff solid; border-radius: 50%;" />';

	$ret .= '<div class="card-item">';

	$mainUi = new Ui(NULL, 'ui-menu');


	if (i()->ok) {
		$mainUi->add('<h3>My Account</h3>');
		$mainUi->add('<a class="sg-action" href="'.url('imed/app/my/profile/info').'" data-rel="box" data-webview="Change Account Profile" data-width="480" data-max-height="80%"><i class="icon -person"></i><span>{tr:Change Your Profile Details}</span></a>');
		$mainUi->add('<a class="sg-action" href="'.url('my/change/password').'" data-rel="box" data-webview="Change Password" data-width="480" data-max-height="80%"><i class="icon -invisible"></i><span>{tr:Change Password}</span></a>');
		$mainUi->add('<a class="sg-action" href="'.url('signout').'" data-rel="none" data-title="ออกจากระบบสมาชิก" data-confirm="ต้องการออกจากระบบสมาชิก กรุณายืนยัน?" data-done="reload:'.url('flood/climate/menu').'"><i class="icon -material">lock_open</i><span>{tr:Sign out}</span></a>');
	}

	$mainUi->add('<h3>MENU</h3>');

	$mainUi->add('<a class="sg-action" href="{url:news}" data-webview="สถานการณ์ลุ่มน้ำ" data-webview-history="true"><i class="icon -star"></i><span>สถานการณ์ลุ่มน้ำ</span></a>');
	$mainUi->add('<a class="sg-action" href="http://www.songkhla.tmd.go.th/RF/Monitor/" data-webview="น้ำฝนออนไลน์"><i class="icon -star"></i><span>รายงานออนไลน์ปริมาณน้ำฝนในลุ่มน้ำคลองอู่ตะเภา</span></a>');
	$mainUi->add('<a class="sg-action" href="http://www.songkhla.tmd.go.th/index.php?modules=forecast" data-webview="พยากรณ์อากาศ"><i class="icon -star"></i><span>พยากรณ์อากาศภาคใต้ฝั่งตะวันออก</span></a>');

	$mainUi->add('<a class="sg-action" href="http://www.songkhla.tmd.go.th/RadarSat/radar/stp_last_bkk.html" data-webview="เรดาห์"><i class="icon -star"></i><span>ภาพเรดาห์ฯ สทิงพระ ล่าสุด</span></a>');

	$mainUi->add('<a class="sg-action" href="http://www.songkhla.tmd.go.th/RadarSat/radar/stp_loop_bkk.html" data-webview="เรดาห์"><i class="icon -star"></i><span>ภาพเรดาห์ฯ สทิงพระ เคลื่อนไหว</span></a>');

	$mainUi->add('<a class="sg-action" href="{url:flood/app/cam/20}" data-rel="none" data-webview="true" data-webview="แผนที่อากาศ"><i class="icon -star"></i><span>แผนที่อากาศ</span></a>');

	$ret .= $mainUi->build();

	$ret .= '</div>';

	$otherUi = new Ui();
	$otherUi->addConfig('nav', '{class: "nav -app-menu"}');
	$otherUi->header('<h3>อื่นๆ</h3>');

	$otherUi->add('<a class="sg-action" href="'.url('my/clear/cache').'" data-webview="ล้างแคช" data-options=\'{clearCache: true}\' data-rel="box" data-width="480"><i class="icon -material">clear</i><span>ล้างแคช</span></a>');

	$ret .= $otherUi->build();

	if ($isAdmin) {
		$adminUi = new Ui();
		$adminUi->addConfig('nav', '{class: "nav -app-menu"}');
		$adminUi->header('<h3>ผู้จัดการระบบ</h3>');

		if (i()->username == 'softganz') {
			if (_DOMAIN_SHORT == 'localhost') {
				if (R()->appAgent) {
					$adminUi->add('<a href="'.url('app',array('setting:app' => '{}')).'"><i class="icon -material">web</i><span>www</span></a>');
				} else {
					$adminUi->add('<a href="'.url('app',array('setting:app' => '{OS:%22Android%22,ver:%220.3.00%22,type:%22App%22,dev:%22Softganz%22}')).'"><i class="icon -material">android</i><span>App</span></a>');
				}
			}

			if (R()->appAgent->OS == 'Android') {
				$host = preg_match('/^([a-z]+)/', _DOMAIN_SHORT, $out) ? $out[1] : _DOMAIN_SHORT;
				$isProduction = $host == 'communeinfo';
				$adminUi->add('<a class="sg-action" data-rel="none" data-webview="server" data-server="'.($isProduction ? 'DEV' : 'PRODUCTION').'" data-done="load:#main"><i class="icon -material">android</i><span>'.strtoupper($host).'</span></a>');
			}
		}

		$ret .= $adminUi->build();
	}

	head(
	'<script type="text/javascript"><!--
		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM WEBVIEW")
			var options = {actionBar: true, navBar: true}
			return options
		}
	</script>'
	);

	$ret .= '<style type="text/css">
	.fileinput-button {border:none; background: #fff; box-shadow: none;}
	</style>';
	return $ret;
}
?>