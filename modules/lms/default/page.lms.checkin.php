<?php
/**
* LMS :: View Classroom Check In
* Created 2020-07-01
* Modify  2020-07-11
*
* @param Object $self
* @param Object $courseInfo
* @param Int $moduleId
* @return String
*/

$debug = true;

function lms_checkin($self, $courseInfo = NULL, $moduleId = NULL) {
	R::View('toolbar', $self, 'Check In'.($courseInfo->name ? '/' : '').$courseInfo->name, 'lms', $courseInfo, '{searchform: false}');

	$ret .= '<div id="lms-checkin" class="lms-checkin" data-url="'.url('lms/checkin').'">';

	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>เช็คอิน</h3></header>';

	//setcookie('lms.checkin',NULL,time()-1000,'/');

	//$ret .= print_o(cfg(),'cfg()');
	/*
	if ($_COOKIE['lms_checkin']) {
		$ret .= __lms_checkin_phone_checkout($_COOKIE['lms_checkin']);
	} else
	*/

	if (!i()->ok) {
		//$ret .= __lms_checkin_phone();
		//$ret .= '<p class="-sg-text-center">หรือเช็คอินด้วยระบบสมาชิก</p>';
		$ret .= R::View('signform', '{time:-1, rel: "none", done: "load | load->clear:box:'.url('lms/checkin').'"}');
	} else if ($checkInList = __lms_checkin_ready()) {
		$ret .= __lms_checkin_checkout($checkInList);
	} else if ($checkinOpen = __lms_checkin_open()) {
		$ret .= __lms_checkin($checkinOpen);
	} else {
		$ret .= '<div style="padding: 0px 0; text-align: center;"><p class="notify" style="padding: 32px 0; margin: 0;"><i class="icon -material -sg-64">error_outline</i><br /><br />ไม่มีห้องเรียนเปิดให้เช็คอินในเวลานี้</p></div>';
	}

	//$classInfo = R::Model('lms.class.get',1);
	//$ret .= print_o($classInfo);

	/*
	if ($checkInList = __lms_checkin_ready()) {
		$ret .= __lms_checkin_checkout($checkInList);
	} else if ($courseInfo && $moduleId) {
		$ret .= __lms_checkin_module($courseId, $moduleId);
	} else if ($courseInfo) {
		$ret .= __lms_checkin_module($courseId);
	} else {
		$ret .= __lms_checkin_module();
	}
	*/

	//if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');
	/*
	$courseId = $courseInfo->courseId;

	$isAdmin = user_access('administer lms');
	$currentDate = date('Y-m-d H:i:s');

	$isCheckInDate = $courseInfo->info->dateregfrom && $courseInfo->info->dateregend && $currentDate >= $courseInfo->info->dateregfrom && $currentDate <= $courseInfo->info->dateregend;
	*/

	// Show Check In QR Code
	$qrCodeStyle = '{width: 512, height: 512, imgWidth: "200px", imgHeight: "200px"}';
	if ($courseInfo && $moduleId) {
		$checkInUrl = url('lms/'.$courseId.'/checkin/'.$moduleId);
	} else if ($courseInfo) {
		$checkInUrl = url('lms/'.$courseId.'/checkin');
	} else {
		$checkInUrl = url('lms/checkin');
	}
	$qrCode = SG\qrcode($checkInUrl,$qrCodeStyle);

	$ret .= '<div class="qrcode" style="margin: 0 auto; padding: 16px;">'
		. $qrCode.'<br />'
		//. '<a class="btn -link" href="'.$checkInUrl.'">Check In</a><br />'
		. '</div>';

	//$ret .= $moduleId.print_o($courseInfo,'$courseInfo');

	$ret .= '<style type="text/css">
	.lms-checkin-card>.ui-item {padding: 32px; margin: 16px;}
	.qrcode {text-align: center; border-top: 1px #eee solid;}
	.lms-checkin-card .btn.-primary {margin-top: 32px; padding: 16px 32px;}
	.lms-checkin-card .btn.-check-out {margin-top: 32px; padding: 16px 32px;}
	.qrcode>img {display: block; margin: 0 auto;}
	.login, #cboxContent .login {width: 280px; margin: 0 auto; background-color: transparent; border: none;}
	.login .-info {display: none;}
	.login .-form>h3 {display: none;}
	.login .-form>h5 {display: none;}
	.login .-form>ul {display: none;}
	.login .-form {width: 100%; margin: 0;}
	.login .-form .signform>.ui-action>a:first-child {display: none;}
	.box-page .login .signform {padding: 0;}
	</style>';

	$ret .= '</div><!-- lms-checkin -->';
	return $ret;
}

function __lms_checkin_ready($phone = NULL) {
	mydb::where('ck.`timein` IS NOT NULL AND ck.`timeout` IS NULL');
	if ($phone) {
		mydb::where('ck.`phone` = :phone', ':phone', $phone);
	} else {
		mydb::where('ck.`uid` = :uid', ':uid', i()->uid);
	}

	$stmt = 'SELECT
		ck.`chkid`, ck.`uid`
		, ck.`timein`, ck.`timeout`
		, ck.`courseid`, ck.`modid`
		, t.`title`
		, m.`name` `moduleName`, m.`enname` `enModuleName`
		, c.`name` `courseName`
		FROM %lms_checkin% ck
			LEFT JOIN %lms_student% s USING(`sid`)
			LEFT JOIN %lms_timetable% t ON t.`classid` = ck.`classid`
			LEFT JOIN %lms_course% c ON c.`courseid` = ck.`courseid`
			LEFT JOIN %lms_mod% m ON m.`modid` = ck.`modid`
		%WHERE%
		';
	$dbs = mydb::select($stmt);
	//echo mydb()->_query;
	//debugMsg(mydb()->_query);
	//debugMsg($dbs);
	return $dbs->items;
}

function __lms_checkin_open() {
	mydb::where('(t.`openbeforemin` >= 0) AND ( :curtime BETWEEN DATE_SUB(t.`start`, INTERVAL t.`openbeforemin` MINUTE) AND DATE_ADD(t.`end`, INTERVAL t.`openaftermin` MINUTE) )', ':curtime', date('Y-m-d H:i:s'));
	mydb::where('s.`status` = "Active"', ':uid', i()->uid);

	$stmt = 'SELECT
		t.*
		, s.`sid`, s.`uid`, s.`status` `studentStatus`
		, c.`name` `courseName`
		, m.`name` `moduleName`, m.`enname` `enModuleName`
		FROM %lms_timetable% t
			LEFT JOIN %lms_student% s ON s.`courseid` = t.`courseid` AND s.`uid` = :uid AND s.`status` = "Active"
			LEFT JOIN %lms_course% c ON c.`courseid` = t.`courseid`
			LEFT JOIN %lms_mod% m ON m.`modid` = t.`modid`
		%WHERE%
		';

	$dbs = mydb::select($stmt);
	return $dbs->items;
}

function __lms_checkin($classList = NULL) {
	$cardUi = new Ui('div', 'ui-card lms-checkin-card -sg-text-center');

	foreach ($classList as $rs) {
		$cardUi->add(
			'<b>ห้องเรียน "'.$rs->title.'"</b><br />'
			. 'เริ่มเวลา '.sg_date($rs->start, 'H:i').' น.'
			. '<nav class="nav -card -sg-text-center"><a class="sg-action btn -primary" href="'.url('lms/'.$rs->courseid.'/info/mod.checkin/'.$rs->classid).'" data-rel="notify" data-done="load->replace:.lms-checkin" data-title="เช็คอิน" data-confirm="ต้องการเช็คอินเข้าห้องเรียน"><i class="icon -material">login</i><span>เช็คอิน</span></a></nav>'
			. '<br />รายวิชา '.$rs->moduleName.' '.($rs->enModuleName ? '( '.$rs->enModuleName.' )' : '').'<br />'
			. 'หลักสูตร '.$rs->courseName
		);
	}

	$ret .= $cardUi->build();

	return $ret;
}

function __lms_checkin_checkout($checkInList) {
	$lmsConfig = cfg('lms');
	$zoomLink = $lmsConfig->online->link;

	$cardUi = new Ui('div', 'ui-card lms-checkin-card -sg-text-center');
	foreach ($checkInList as $rs) {
		$onlineUi = new Ui();
		$onlineUi->addConfig('nav', '{class: "nav -card -sg-text-center"}');

		foreach ($lmsConfig->online as $onlineItem) {
			$onlineUi->add('<a class="btn -primary" href="'.$onlineItem->link.'" target="_blank"><i class="icon -material -sg-48">duo</i><span>'.$onlineItem->title.'</span></a>');
		}

		$cardUi->add(
			'<b>ห้องเรียน "'.$rs->title.'"</b>'
			. $onlineUi->build()
			. '<br />รายวิชา '.$rs->moduleName.' '.($rs->enModuleName ? '( '.$rs->enModuleName.' )' : '').'<br />'
			. 'หลักสูตร '.$rs->courseName
			. '<nav class="nav -card -sg-text-center"><a class="sg-action btn -check-out" href="'.url('lms/'.$rs->courseid.'/info/mod.checkout/'.$rs->chkid).'" data-rel="notify" data-done="reload" data-title="เช็คเอ้าท์" data-confirm="ต้องการเช็คเอ้าท์ออกจากห้องเรียน"><i class="icon -material">logout</i><span>เช็คเอ้าท์</span></a></nav>'
		);
	}

	$ret .= $cardUi->build();

	$ret .= '<p class="notify">ท่านมีการเช็คอินอยู่แล้ว หากต้องการเช็คอินรายวิชาอื่น กรุณาเช็คเอ้าท์ก่อน</p>';

	return $ret;
}

function __lms_checkin_phone() {
	mydb::where('(t.`openbeforemin` >= 0) AND (:curtime BETWEEN DATE_SUB(t.`start`, INTERVAL t.`openbeforemin` MINUTE) AND t.`end`)', ':curtime', date('Y-m-d H:i:s'));
	//mydb::where('s.`status` = "Active"', ':uid', i()->uid);

	$stmt = 'SELECT
		t.*
		, c.`name` `courseName`
		, m.`name` `moduleName`, m.`enname` `enModuleName`
		FROM %lms_timetable% t
			LEFT JOIN %lms_course% c ON c.`courseid` = t.`courseid`
			LEFT JOIN %lms_mod% m ON m.`modid` = t.`modid`
		%WHERE%
		';

	$classList = mydb::select($stmt)->items;

	$cardUi = new Ui('div', 'ui-card lms-checkin-card -sg-text-center');

	foreach ($classList as $rs) {
		$cardUi->add(
			'<h3>เช็คอินด้วยเบอร์โทร</h3><b>ห้องเรียน "'.$rs->title.'"</b><br />'
			. 'เริ่มเวลา '.sg_date($rs->start, 'H:i').' น.'
			. '<form class="sg-form" action="'.url('lms/'.$rs->courseid.'/info/mod.checkin/'.$rs->classid, array('confirm'=>'yes')).'" data-checkvalid="true" data-rel="notify" data-done="load->replace:.lms-checkin" data-title="เช็คอิน" style=" width: 260px; margin: 0 auto 32px;">'
			. '<div class="form-item"><label for="edit-phone" class="-hidden">เบอร์โทร</label><input id="edit-phone" class="form-text -fill -require" type="text" name="phone" placeholder="ระบุเบอร์โทร" style="margin: 8px; padding: 16px;" /><button class="btn -primary -fill"><i class="icon -material">phone</i><span>เช็คอินด้วยเบอร์โทร</a></button>'
			. '</form>'
			//. '<nav class="nav -card -sg-text-center"><a class="sg-action btn -primary" href="'.url('lms/'.$rs->courseid.'/info/mod.checkin/'.$rs->classid).'" data-rel="notify" data-done="load:box:'.url('lms/checkin',array('complete'=>'yes')).'" data-title="เช็คอิน" data-confirm="ต้องการเช็คอินเข้าห้องเรียน"><i class="icon -material">login</i><span>เช็คอิน</span></a></nav>'
			. '<br />รายวิชา '.$rs->moduleName.' '.($rs->enModuleName ? '( '.$rs->enModuleName.' )' : '').'<br />'
			. 'หลักสูตร '.$rs->courseName
		);
	}

	$ret .= $cardUi->build();

	/*
	$ret .= '<nav class="nav -sg-text-center" style="padding: 32px 0; border-bottom: 1px #eee solid;">'
		. '<p>เช็คอินด้วยเบอร์โทร</p>'
		. '<form action="'.url('lms/checkin').'" style=" width: 260px; margin: 0 auto 32px;"><input class="form-text -fill" type="text" name="phone" placeholder="ระบุเบอร์โทร" style="margin-bottom: 16px;" /><button class="btn -primary -fill"><i class="icon -material">phone</i><span>เช็คอินด้วยเบอร์โทร</a></button></form>'
		. '</nav>';
		*/

	return $ret;
}

function __lms_checkin_phone_checkout($phone) {
	$lmsConfig = cfg('lms');
	$zoomLink = $lmsConfig->online->link;

	$checkInList =  __lms_checkin_ready($phone);

	$cardUi = new Ui('div', 'ui-card lms-checkin-card -sg-text-center');
	foreach ($checkInList as $rs) {
		$cardUi->add(
			'<b>ห้องเรียน "'.$rs->title.'"</b>'
			. '<nav class="nav -card -sg-text-center"><a class="btn -primary" href="'.$zoomLink.'" target="_blank"><i class="icon -material -sg-48">duo</i><span>เข้าห้องเรียนออนไลน์<br />(ZOOM)</span></a></nav>'
			. '<br />รายวิชา '.$rs->moduleName.' '.($rs->enModuleName ? '( '.$rs->enModuleName.' )' : '').'<br />'
			. 'หลักสูตร '.$rs->courseName
			. '<nav class="nav -card -sg-text-center"><a class="sg-action btn -check-out" href="'.url('lms/'.$rs->courseid.'/info/mod.checkout/'.$rs->chkid).'" data-rel="notify" data-done="reload" data-title="เช็คเอ้าท์" data-confirm="ต้องการเช็คเอ้าท์ออกจากห้องเรียน"><i class="icon -material">logout</i><span>เช็คเอ้าท์</span></a></nav>'
		);
	}

	$ret .= $cardUi->build();

	$ret .= '<p class="notify">ท่านมีการเช็คอินอยู่แล้ว หากต้องการเช็คอินรายวิชาอื่น กรุณาเช็คเอ้าท์ก่อน</p>';

	return $ret;
}







//TODO : Not Used
function __lms_checkin_module($courseId = NULL, $moduleId = NULL) {

	mydb::where('s.`uid` = :uid AND s.`status` IN ("Active")', ':uid', i()->uid);
	if ($courseId) mydb::where('c.`courseid` = :courseId', ':courseId', $courseId);
	if ($moduleId) mydb::where('c.`modid` = :modId', ':modId', $moduleId);

	$stmt = 'SELECT
		s.`uid`, s.`courseid`, c.`modid`
		, m.`name` `moduleName`, m.`enname` `enModuleName`
		FROM %lms_student% s
			LEFT JOIN %lms_cmod% c USING(`courseid`)
			LEFT JOIN %lms_mod% m USING(`modid`)
		%WHERE%
		';
	$dbs = mydb::select($stmt);

	$ret .= '<h3>เลือกรายวิชา</h3>';

	$cardUi = new Ui('div', 'ui-card lms-checkin-card -sg-text-center');

	foreach ($dbs->items as $rs) {
		$cardUi->add(
			'<b>'.$rs->moduleName.'</b><br />'.$rs->enModuleName
			. '<nav class="nav -card -sg-text-center"><a class="sg-action btn -primary" href="'.url('lms/'.$rs->courseid.'/info/mod.checkin/'.$rs->modid).'" data-rel="notify" data-done="load::'.url('lms/checkin',array('complete'=>'yes')).'" data-title="เช็คอิน" data-confirm="ต้องการเช็คอินเข้าห้องเรียน"><i class="icon -material">login</i><span>เช็คอิน</span></a></nav>'
		);
	}

	$ret .= $cardUi->build();

	return $ret;
}


?>