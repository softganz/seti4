<?php
/**
* Green :: My Home Menu
* Created 2018-06-15
* Modify  2020-11-30
*
* @param Object $self
* @return String
*
* @usage green/my
*/

$debug = true;

function green_my_home($self) {
	$isWebAdmin = is_admin();
	$isAdmin = is_admin('green');
	$isAccessDev = in_array(i()->username, array('softganz','momo'));
	$isLocalHost = _DOMAIN_SHORT == 'localhost';

	$ui = new Ui();


	$ret = '<div class="imed-patient-photo-wrapper" style="margin:0 0 16px; padding: 16px 0; position:relative; background-color: #fff">';

	$ret .= '<div id="imed-patient-photo" style="width: 196px; height: 196px; margin: 0px auto 8px; display: block; border-radius: 50%; overflow: hidden; border: 2px #eee solid;"><img src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a></div>';

	$ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('my/api/photo.change').'" data-rel="notify" data-done="load:#main:'.url('green/my').'" style="position: absolute; top: 16px; right: calc(50% - 112px); padding: 0; margin: 0; background-color: transparent;"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" x-capture="capture" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>';

	$ret .= '<div class="-sg-text-center"><b>'.i()->name.'</b></div>';
	$ret .= '</div>';

	$mainUi = new Ui();
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');

	$mainUi->header('<h3>My Account</h3>');
	if (i()->username != 'demo') {
		$mainUi->add('<a class="sg-action" href="'.url('green/my/profile/info').'" data-rel="box" data-webview="Change Account Profile" data-width="480" data-max-height="80%"><i class="icon -material">account_circle</i><span>รายละเอียด</span></a>');
		$mainUi->add('<a class="sg-action" href="'.url('my/change/password').'" data-rel="box" data-webview="Change Password" data-width="480" data-max-height="80%"><i class="icon -material">visibility</i><span>{tr:Change Password}</span></a>');
	}
	$mainUi->add('<a class="sg-action" href="'.url('signout').'" data-rel="none" data-title="ออกจากระบบสมาชิก" data-confirm="ต้องการออกจากระบบสมาชิก กรุณายืนยัน?" data-done="reload:'.url('green/my').' | moveto:0,0"><i class="icon -material">lock_open</i><span>{tr:Sign out}</span></a>');


	$ret .= $mainUi->build();


	$mainUi = new Ui();
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');
	$mainUi->header('<h3>รายการของฉัน</h3>');
	$ret .= $mainUi->build();


	$myShopList = R::Model('green.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');
	$hasLand = mydb::select('SELECT COUNT(*) `amt` FROM %ibuy_farmland% WHERE `uid` = :uid LIMIT 1', ':uid', i()->uid)->amt;
	$isOfficer = mydb::select('SELECT `orgid` FROM %org_officer% WHERE `uid` = :uid AND `membership` IN ("ADMIN","OFFICER") LIMIT 1', ':uid', i()->uid)->orgid;

	if ($myShopList) {
		$mainUi = new Ui();
		$mainUi->addConfig('nav', '{class: "nav -app-menu"}');
		$mainUi->header('<h3>เกษตรอินทรีย์</h3>');
		if ($isLocalHost) {
			//$mainUi->add('<a class="sg-action" href="'.url('green').'"><i class="icon -material">home</i><span>หน้าแรก</span></a>');
		}
		$mainUi->add('<a class="sg-action" href="'.url('green/organic/my/land').'" data-webview="แปลงที่ดิน"><i class="icon -material">nature_people</i><span>แปลงที่ดิน</span></a>');
		if ($hasLand) {
			$mainUi->add('<a class="sg-action" href="'.url('green/organic/my/plant').'" data-webview="ปลูกผัก"><i class="icon -material">grass</i><span>ปลูกผัก</span></a>');
		}
		$mainUi->add('<a class="sg-action" href="'.url('green/my/goods').'" data-webview="สินค้า"><i class="icon -material">local_florist</i><span>สินค้า</span></a>');
		$mainUi->add('<a class="sg-action" href="'.url('green/my/follower').'" data-webview="ผู้ติดตาม"><i class="icon -material">stars</i><span>ผู้ติดตาม</span></a>');
		if ($isAdmin) {
			$mainUi->add('<a class="sg-action" href="'.url('green/organic/my/org').'" data-webview="จัดการกลุ่ม"><i class="icon -material">account_balance</i><span>จัดการกลุ่ม</span></a>');
		}

		//$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my').'" data-webview="สวนยางยั่งยืน"><i class="icon -material">nature</i><span>สวนยางยั่งยืน</span></a>');

		$ret .= $mainUi->build();

		$actionUi = new Ui();
		$actionUi->addConfig('nav', '{class: "nav -app-menu"}');
		$actionUi->header('<h3>สวนยางยั่งยืน</h3>');
		//$actionUi->add('<a class="sg-action" href="'.url('green/rubber/my/org').'" data-webview="กลุ่ม"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
		$actionUi->add('<a class="sg-action" href="'.url('green/rubber/my/land').'" data-webview="แปลงสวนยาง"><i class="icon -material">nature_people</i><span>แปลงสวนยาง</span></a>');
		if ($isOfficer || $hasLand) {
			$actionUi->add('<a class="sg-action" href="'.url('green/rubber/my/rubber').'" data-webview="ต้นยาง"><i class="icon -material">nature</i><span>ต้นยาง</span></a>');
			$actionUi->add('<a class="sg-action" href="'.url('green/rubber/my/plant').'" data-webview="พืชผสมผสาน"><i class="icon -material">grass</i><span>พืชผสมผสาน</span></a>');
		}
		$actionUi->add('<a class="sg-action -disabled" href="'.url('green/rubber/my/buy').'" data-webview="รับซื้อน้ำยาง"><i class="icon -material">money</i><span>รับซื้อน้ำยาง</span></a>');

		$ret .= $actionUi->build();

		//$ret .= print_o(mydb::select('SELECT * FROM %org_officer% WHERE `uid` = :uid', ':uid', i()->uid), 'OOO');

		//$ret .= '$isOfficer = '.$isOfficer;

		$actionUi = new Ui();
		$actionUi->addConfig('nav', '{class: "nav -app-menu"}');
		$actionUi->header('<h3>แอพพลิเคชั่น</h3>');
		$actionUi->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'" data-webview="ธนาคารต้นไม้"><i class="icon -material">nature</i><span>ธนาคารต้นไม้</span></a>');
		if ($isOfficer || $hasLand) {
			$actionUi->add('<a class="sg-action" href="'.url('green/my/animal').'" data-webview="ปศุสัตว์"><i class="icon -material">emoji_nature</i><span>ปศุสัตว์</span></a>');
		}
		$actionUi->add('<a class="sg-action" href="'.url('bmc').'" data-webview="BMC"><i class="icon -material">money</i><span>BMC</span></a>');
		$actionUi->add('<a class="sg-action -disabled" href="'.url('green/rubber/my/gl').'" data-webview="บัญชีต้นทุน"><i class="icon -material">attach_money</i><span>บัญชีต้นทุน</span></a>');
		$ret .= $actionUi->build();

	} else {
		$bannerUi = new Ui();
		$bannerUi->addConfig('nav', '{class: "nav -app-menu -banner"}');
		//$bannerUi->add('<a class="sg-action" href="'.url('green/my/rubber').'"><img src="//img.softganz.com/img/pa-plan-20.jpg" />เปิดร้านขายของ</a>');
		$bannerUi->add('<a class="sg-action" href="'.url('green/organic/register').'"><img src="https://communeinfo.com/upload/pics/green-banner-1.jpg" /><span>สมัครใช้งานเกษตรอินทรีย์</span></a>');
		$bannerUi->add('<a class="sg-action" href="'.url('green/rubber/register').'"><img src="https://communeinfo.com/upload/photo/green-rubber-banner-01.jpg" /><span>สมัครใช้งานสวนยางยั่งยืน</span></a>');
		$ret .= $bannerUi->build();
	}

	if ($isAdmin) {
		$bannerUi = new Ui();
		$bannerUi->addConfig('nav', '{class: "nav -app-menu -banner"}');
		//$bannerUi->add('<a class="sg-action" href="'.url('green/my/rubber').'"><img src="//img.softganz.com/img/pa-plan-20.jpg" />เปิดร้านขายของ</a>');
		$bannerUi->add('<a class="sg-action" href="'.url('green/organic/register').'"><img src="https://communeinfo.com/upload/pics/green-banner-1.jpg" /><span>สมัครใช้งานเกษตรอินทรีย์</span></a>');
		$bannerUi->add('<a class="sg-action" href="'.url('green/rubber/register').'"><img src="https://communeinfo.com/upload/photo/green-rubber-banner-01.jpg" /><span>สมัครใช้งานสวนยางยั่งยืน</span></a>');
		$ret .= $bannerUi->build();
	}

	$otherUi = new Ui();
	$otherUi->addConfig('nav', '{class: "nav -app-menu"}');
	$otherUi->header('<h3>อื่นๆ</h3>');

	$otherUi->add('<a class="sg-action" href="'.url('my/clear/cache').'" data-webview="ล้างแคช" data-options=\'{clearCache: true}\' data-rel="box" data-width="480"><i class="icon -material">clear</i><span>ล้างแคช</span></a>');
	$otherUi->add('<a class="sg-action" href="'.url('green/my/info/shop.clear').'" data-rel="notify" xdata-done="reload"><i class="icon -material">cancel</i><span>Clear Shop</span></a>');

	$ret .= $otherUi->build();


	if ($isWebAdmin) {
		$adminUi = new Ui();
		$adminUi->addConfig('nav', '{class: "nav -app-menu"}');
		$adminUi->header('<h3>ผู้จัดการระบบ</h3>');

		$adminUi->add('<a href="'.url('imed/admin/member').'"><i class="icon -material">groups</i><span>สมาชิก</span></a>');

		if (i()->username == 'softganz') {
			if (_DOMAIN_SHORT == 'localhost') {
				if (R()->appAgent) {
					$adminUi->add('<a href="'.url('green',array('setting:app' => '{}')).'"><i class="icon -material">web</i><span>www</span></a>');
				} else {
					$adminUi->add('<a href="'.url('green',array('setting:app' => '{OS:%22Android%22,ver:%220.3.00%22,type:%22App%22,dev:%22Softganz%22}')).'"><i class="icon -material">android</i><span>App</span></a>');
				}
			}

			$adminUi->add('<a href="'.url('imed/app').'"><i class="icon"><img src="//communeinfo.com/themes/default/logo-homemed.png" width="24" height="24" /></i><span>iMed@Home</span></a>');
			$adminUi->add('<a href="https://communeinfo.com"><i class="icon"><img src="//communeinfo.com/themes/default/logo-homemed.png" width="24" height="24" /></i><span>CommuneInfo</span></a>');
			$adminUi->add('<a href="https://hatyaicityclimate.org/flood/climate/feed"><i class="icon -material">videocam</i><span>City Climate</span></a>');

			if (R()->appAgent->OS == 'Android') {
				$host = preg_match('/^([a-z]+)/', _DOMAIN_SHORT, $out) ? $out[1] : _DOMAIN_SHORT;
				$isProduction = $host == 'communeinfo';
				$adminUi->add('<a class="sg-action" data-rel="none" data-webview="server" data-server="'.($isProduction ? 'DEV' : 'PRODUCTION').'" data-done="load:#main"><i class="icon -material">android</i><span>'.strtoupper($host).'</span></a>');
			}
		}


		$ret .= $adminUi->build();
	}

	return $ret;
}
?>