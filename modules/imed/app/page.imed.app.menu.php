<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_app_menu($self, $orgId = NULL) {
	R::View('imed.toolbar',$self,'@'.i()->name,'app');

	$isAdmin = is_admin('imeds');
	$ui = new Ui();

	//$ret .= '<header class="header"><h3>'.i()->name.'</h3><nav class="nav">'.$ui->build().'</nav></header>';

	if (!i()->ok) return R::View('signform', '{time:-1}');

	$userZone = imed_model::get_user_zone(i()->uid,'imed');


	$ret .= '<div class="imed-patient-photo-wrapper" style="margin:0 0 16px; padding: 48px 0 16px; position:relative; background-color: #fff">';
	$ret .= '<div id="imed-patient-photo" style="width: 196px; height: 196px; margin: 0px auto 32px; display: block; border-radius: 50%; overflow: hidden; border: 2px #eee solid;"><img src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a></div>';

	$ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('my/api/photo.change').'" data-rel="notify" data-done="load:#main:'.url('imed/app/menu').'" style="position: absolute; top: 16px; right: calc(50% - 112px); padding: 0; margin: 0; background-color: transparent;"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" x-capture="capture" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>';

	$ret .= '</div>';

	//$ret .= '<img src="'.model::user_photo(i()->username).'" width="200" height="200" style="display: block; margin: 32px auto 32px; border: 2px #fff solid; border-radius: 50%;" />';

	$ret .= '<div class="card-item">';

	$mainUi = new Ui(NULL, 'ui-menu');

	$mainUi->add('<h3>พื้นที่รับผิดชอบ</h3>');

	if ($userZone) {
		foreach ($userZone as $zone) {
			$mainUi->add('<a>'.SG\implode_address($zone,'short').'('.$zone->right.')</a>');
		}
	} else {
		$mainUi->add('<a>ไม่กำหนดพื้นที่</a>');
	}

	//$ret .= '<h4></h4><ul>'.($zoneStr?$zoneStr:'ไม่กำหนดพื้นที่').'</ul>';

	/*
	$ui = new Ui(NULL, 'ui-menu');

	if (i()->ok) {
		$ui->add('<a class="sg-action" href="'.url('my').'" data-webview="'.i()->name.'"><img src="'.model::user_photo(i()->username).'" width="24" height="24" style="vertical-align: middle; display: inline-block; margin-right: 4px;" /><span>{tr:My Account}</span></a>');
		$ui->add('<a class="" href="'.url('signout',array('ret_url'=>'imed/app')).'"><i class="icon -unlock"></i><span>Sign Out</span></a>');
	} else {
		$ui->add('<a href="'.url('imed/app').'"><i class="icon -signin"></i><span>Sign In</span></a>');
	}
	*/

	//$self->theme->sidebar .= $ui->build();

	$mainUi->add('<h3>My Account</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/app/my/profile/info').'" data-rel="box" data-webview="Change Account Profile" data-width="480" data-max-height="80%"><i class="icon -person"></i><span>{tr:Change Your Profile Details}</span></a>');
	if (i()->username != 'demo') {
		$mainUi->add('<a class="sg-action" href="'.url('my/change/password').'" data-rel="box" data-webview="Change Password" data-width="480" data-max-height="80%"><i class="icon -invisible"></i><span>{tr:Change Password}</span></a>');
	}
	//$mainUi->add('<a class="sg-action" href="'.url('imed/app/signout').'"><i class="icon -material">lock_open</i><span>{tr:Sign out}</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('signout').'" data-rel="none" data-title="ออกจากระบบสมาชิก" data-confirm="ต้องการออกจากระบบสมาชิก กรุณายืนยัน?" data-callback="'.url('imed/app').'"><i class="icon -material">lock_open</i><span>{tr:Sign out}</span></a>');

	/*
	$mainUi->add('<h3>My Actions</h3>');
	$mainUi->add('<a href="'.url('my/doc').'"><i class="icon -description"></i><span>My Documents</span></a>');
	$mainUi->add('<a href="'.url('my/like').'"><i class="icon -thumbup"></i><span>My Likes</span></a>');
	$mainUi->add('<a href="'.url('my/bookmark').'"><i class="icon -favorite"></i><span>My Bookmarks</span></a>');
	$mainUi->add('<a href="'.url('my/view').'"><i class="icon -viewdoc"></i><span>My Views</span></a>');
	$mainUi->add('<a href="'.url('my/photo').'"><i class="icon -viewdoc"></i><span>My Photos</span></a>');
	*/

	//$mainUi->add('<h3>รายงาน</h3>');
	//$mainUi->add('<a class="sg-action" href="'.url('imed/report').'" data-rel="box" data-webview="รายงาน" data-width="480" data-height="80%"><i class="icon"></i><span>รายงานผลปฎิบัติงาน</span></a>');

	$mainUi->add('<h3>รายงานตามพื้นที่</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/report/disabledarea').'" data-webview="รายงานคนพิการแยกตามพื้นที่">รายงานคนพิการแยกตามพื้นที่</a>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/report/elderarea').'" data-webview="รายงานผู้สูงอายุแยกตามพื้นที่">รายงานผู้สูงอายุแยกตามพื้นที่</a>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/report/poormanarea').'" data-webview="รายงานคนยากลำบากแยกตามพื้นที่">รายงานคนยากลำบากแยกตามพื้นที่</a>');


	$mainUi->add('<h3>รายงานคนพิการ</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/report/disabledarea').'" data-webview="รายงานคนพิการแยกตามพื้นที่">รายงานคนพิการแยกตามพื้นที่</a>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/report/homevisit').'" data-webview="รายงานการเยี่ยมบ้าน">รายงานการเยี่ยมบ้าน</a>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/report/newdisability').'"data-webview="รายชื่อคนพิการรายใหม่">รายชื่อคนพิการรายใหม่</a>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/report/prosthetic').'"data-webview="รายงานการได้รับกายอุปกรณ์">รายงานการได้รับกายอุปกรณ์</a>');
	$mainUi->add('<a class="sg-action" href="'.url('imed/report/regexpire').'"data-webview="รายงานวันที่บัตรหมดอายุ">รายงานวันที่บัตรหมดอายุ</a>');
	//$ui->add('<a class="sg-action" href="'.url('imed/report/localmoney').'" data-rel="#imed-app">รายงานได้รับเบี้ยยังชีพคนพิการ</a>');

	$otherUi = new Ui();
	$otherUi->addConfig('nav', '{class: "nav -app-menu"}');
	$otherUi->header('<h3>อื่นๆ</h3>');

	$otherUi->add('<a class="sg-action" href="'.url('my/clear/cache').'" data-webview="ล้างแคช" data-options=\'{clearCache: true}\' data-rel="box" data-width="480"><i class="icon -material">clear</i><span>ล้างแคช</span></a>');

	$ret .= $otherUi->build();

	if ($isAdmin) {
		$mainUi->add('<h3>ผู้จัดการระบบ</h3>');

		//$mainUi->add('<a class="sg-action" href="'.url('imed/admin/activity').'" data-rel="#imed-app"><i class="icon -material">playlist_add_check</i><span>กิจกรรมการแก้ไขข้อมูล</span></a>');
		//$mainUi->add('<a class="sg-action" href="'.url('imed/admin/visit').'" data-rel="#imed-app"><i class="icon -material">chat</i><span>บันทึกการเยี่ยมบ้าน</span></a>');
		$mainUi->add('<a class="sg-action" href="'.url('imed/admin/member').'" data-rel="#main" data-webview="จัดการสมาชิก"><i class="icon -material">people</i><span>จัดการสมาชิก-สิทธิ์การเข้าถึง</span></a>');
		//$mainUi->add('<a class="sg-action" href="'.url('imed/admin/checkqt').'" data-rel="#imed-app"><i class="icon -material">playlist_add</i><span>ตรวจสอบแบบสอบถาม</span></a>');
		//$mainUi->add('<a class="sg-action" href="'.url('imed/report/newperson').'" data-rel="#imed-app">รายชื่อมาใหม่</a>');
		//$mainUi->add('<a class="sg-action" href="'.url('imed/report/newdisability').'" data-rel="#imed-app">รายชื่อคนพิการใหม่</a>');
		//$mainUi->add('<a class="sg-action" href="'.url('imed/report/haveqt').'" data-rel="#imed-app">รายชื่อคนพิการป้อนแบบสอบถาม</a>');
		//$mainUi->add('<a class="sg-action" href="'.url('imed/report/addqtbyuser').'" data-rel="#imed-app">รายชื่อคนพิการป้อนแบบสอบถามตามผู้ป้อน</a>');


		//$mainUi->add('<a class="sg-action" href="'.url('imed/admin/member/zone').'" data-rel="#imed-app">ผู้มีสิทธิ์เข้าถึงข้อมูลพื้นที่</a>');

		//$mainUi->add('<a class="sg-action" href="'.url('imed/admin/stkcode').'" data-rel="#imed-app">รหัสกายอุปกรณ์ / แผนการดูแล</a>');
	}

	$ret .= $mainUi->build();

	/*
	$ret .= '<div class="imed-sidebar -status -no-print">';
	$ret .= $ui->build();
	$ret .= '</div>';
	*/

	$ret .= '</div>';
	return $ret;
}
?>