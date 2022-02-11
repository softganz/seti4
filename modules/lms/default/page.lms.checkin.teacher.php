<?php
/**
* LMS :: Check In By Teacher
* Created 2020-07-03
* Modify  2020-07-03
*
* @param Object $self
* @param Object $courseInfo
* @return String
*/

$debug = true;

function lms_checkin_teacher($self, $courseInfo = NULL, $moduleId = NULL) {
	R::View('toolbar', $self, 'Check In'.($courseInfo->name ? '/' : '').$courseInfo->name, 'lms', $courseInfo, '{searchform: false}');

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$classId = post('cid');

	$isAdmin = user_access('administer lms');
	$isTeacher = user_access('teacher lms');

	if (!($isAdmin || $isTeacher)) return message('error', 'Access Denied');

	$currentDate = date('Y-m-d');

	$isCheckIn = $courseInfo->info->datebegin && $courseInfo->info->dateend && $currentDate >= $courseInfo->info->datebegin && $currentDate <= $courseInfo->info->dateend;

	if (!$isCheckIn) return message('error', 'ไม่อยู่ในช่วงเวลาหลักสูตรเปิดทำการสอน');

	if (empty($moduleId)) {
		$ret = '<header class="header"><h3>Check In : '.$courseInfo->name.'</h3></header>';
		$stmt = 'SELECT c.*, m.`name` `moduleName`, m.`enname` `enModuleName`
			FROM %lms_cmod% c
				LEFT JOIN %lms_mod% m USING(`modid`)
			WHERE c.`courseid` = :courseId
			';
		$dbs = mydb::select($stmt, ':courseId', $courseId);

		$cardUi = new Ui('div', 'ui-card lms-checkin-teacher -class-list');

		foreach ($dbs->items as $rs) {
			$cardUi->add(
				'<div class="detail">'
				. '<b>'.$rs->moduleName.'</b><br />'.$rs->enModuleName
				. '</div>'
				. '<div class="menu">'
				. '<nav class="nav -card -sg-text-center"><a class="btn -primary" href="'.url('lms/'.$rs->courseid.'/checkin.teacher/'.$rs->modid).'"><i class="icon -material">login</i><span>เลือกวิชา</span></a></nav>'
				. '</div>',
				'{class: "-sg-flex -sg-text-center"}'
			);
		}
		$ret .= $cardUi->build();
	} else if (empty($classId)) {
		$ret = '<header class="header"><h3>Check In : '.$courseInfo->name.'</h3></header>';
		$stmt = 'SELECT
			t.*
			, COUNT(ck.`chkid`) `totalCheckin`
			, COUNT(ck.`timeout`) `totalChekOut`
			FROM %lms_timetable% t
				LEFT JOIN %lms_checkin% ck USING(`classid`)
			WHERE t.`courseid` = :courseId AND t.`modid` = :moduleId
			-- AND `openbeforemin` >= 0
			GROUP BY `classid`
			ORDER BY `start`';
		$dbs = mydb::select($stmt, ':courseId', $courseId, ':moduleId', $moduleId);

		$cardUi = new Ui('div', 'ui-card lms-checkin-teacher -class-list');

		foreach ($dbs->items as $rs) {
			$cardUi->add(
				'<div class="detail">'
				. '<b>'.$rs->title.'</b><br /><br />'
				. sg_date($rs->start, 'ว ดดด ปปปป H:i').' น.'
				. '</div>'
				. '<div class="menu">'
				. 'Check In <b>'.$rs->totalCheckin.'</b>'
				. ' Check Out <b>'.$rs->totalChekOut.'</b><br />'
				. ' Remain <b>'.($rs->totalCheckin - $rs->totalChekOut).'</b> persons.'
				. ($rs->openbeforemin > 0 ? '<nav class="nav -card -sg-text-center"><a class="btn -primary" href="'.url('lms/'.$rs->courseid.'/checkin.teacher/'.$rs->modid, array('cid' => $rs->classid)).'"><i class="icon -material">login</i><span>เลือกห้องเรียน</span></a></nav>' : '')
				. '</div>',
				'{class: "-sg-flex -sg-text-center"}'
			);
		}
		$ret .= $cardUi->build();
		//$ret .= print_o($dbs);
	} else {		
		// Show Student for checkin
		$moduleInfo = R::Model('lms.mod.get', $moduleId);


		mydb::where('(ck.`classid` = :classId OR ck.`classid` IS NULL) AND cm.`courseid` = :courseId AND cm.`modid` = :moduleId', ':courseId', $courseId, ':moduleId', $moduleId, ':classId', $classId);
		mydb::where('s.`status` IN ("Active")');
		$stmt = 'SELECT
			ck.`chkid`, ck.`timein`, ck.`timeout`
			, cm.`courseid`, cm.`modid`, ck.`classid`
			, s.`sid`, s.`uid`, CONCAT(s.`prename`, s.`name`, " ", s.`lname`) `studentName`
			, u.`username`
			FROM %lms_cmod% cm
				LEFT JOIN %lms_student% s USING(`courseid`)
				LEFT JOIN %users% u USING(`uid`)
			--	LEFT JOIN %lms_mod% m USING(`modid`)
				LEFT JOIN %lms_checkin% ck ON ck.`courseid` = cm.`courseid` AND ck.`modid` = cm.`modid` AND ck.`classid` = :classId AND ck.`sid` = s.`sid`
			%WHERE%
			ORDER BY CONVERT(s.`name` USING tis620) ASC, CONVERT(s.`lname` USING tis620) ASC
			';
		$dbs = mydb::select($stmt);


		$cardUi = new Ui('div', 'ui-card');

		$checkInTotal = $checkOutTotal = 0;
		foreach ($dbs->items as $rs) {
			$navUi = new Ui();
			$navUi->addConfig('nav', '{class: "nav -card"}');
			if ($rs->timein) $checkInTotal++;
			if ($rs->timeout) $checkOutTotal++;
			if ($rs->uid) {
				$navUi->add('<a class="sg-action btn -primary'.($rs->timein ? ' -disabled' : '').'" href="'.url('lms/'.$rs->courseid.'/info/mod.checkin/'.$classId, array('sid' => $rs->sid)).'" data-rel="notify" data-done="load" data-title="เช็คอิน" data-confirm="ต้องการเช็คอินเข้าห้องเรียน"><i class="icon -material">login</i><span>เช็คอิน</span></a>');
				$navUi->add('<a class="sg-action btn'.($rs->timein && empty($rs->timeout) ? ' ' : ' -disabled').'" href="'.url('lms/'.$rs->courseid.'/info/mod.checkout/'.$rs->chkid).'" data-rel="notify" data-done="load" data-title="เช็คเอ้าท์" data-confirm="ต้องการเช็คเอ้าท์ออกจากห้องเรียน"><i class="icon -material">logout</i><span>เช็คเอ้าท์</span></a>');
				$navUi->add('<a class="sg-action btn'.($rs->chkid ? '' : ' -disabled').'" href="'.url('lms/'.$rs->courseid.'/info/mod.checkin.clear/'.$rs->chkid).'" data-rel="notify" data-done="load" data-title="ยกเลิกการเช็คอิน" data-confirm="ต้องการยกเลิกการเช็คอิน"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>');
			} else {
				$navUi->add('นักเรียนไม่ได้เป็นสมาชิก (NOT REGISTERED USER)');
			}

			$cardUi->add(
				'<div class="header"><img class="profile-photo" src="'.model::user_photo($rs->username).'" /><b>'.$rs->studentName.'</b></div>'
				. '<div class="detail">'
				. ($rs->timein ? ' <i class="icon -material">login</i>เช็คอิน @'.sg_date($rs->timein, 'Y-m-d H:i').' น.' : '')
				. ($rs->timeout ? ' <i class="icon -material">logout</i>เช็คเอ้าท์ @'.sg_date($rs->timeout, 'Y-m-d H:i').' น.' : '')
				. '</div>'
				//. print_o($rs,'$rs')
				. $navUi->build()
			);
		}

		$classInfo = mydb::select('SELECT * FROM %lms_timetable% WHERE `classid` = :classId LIMIT 1',':classId', $classId);

		$navUi = new Ui();
		$navUi->addConfig('nav', '{class: "nav"}');
		$navUi->add('<a class="sg-action btn" href="'.url('lms/'.$courseId.'/info/mod.checkout.all/'.$moduleId,array('cid' => $classId)).'" data-rel="notify" data-done="load" data-title="เช็คเอ้าท์ทุกคน" data-confirm="ต้องการเช็คเอ้าท์ทุกคนออกจากห้องเรียน กรุณายืนยัน?"><i class="icon -material">logout</i><span>เช็คเอ้าท์ทุกคน</span></a>');

		$ret .= '<header class="header"><h3>Check In ('.number_format($checkInTotal).'/'.number_format($checkOutTotal).'/'.number_format($dbs->count()).') : '.$classInfo->title.' ('.$moduleInfo->name.') </h3>'.$navUi->build().'</header>';

		$ret .= $cardUi->build();

		$ret .= 'Total = '.$dbs->count().' รายการ';
	}


	// Show Check In QR Code
	/*
	$qrCodeStyle = '{width: 512, height: 512, imgWidth: "200px", imgHeight: "200px"}';
	if ($courseInfo && $moduleId) {
		$checkInUrl = url('lms/'.$courseId.'/checkin/'.$moduleId);
	} else if ($courseInfo) {
		$checkInUrl = url('lms/'.$courseId.'/checkin');
	} else {
		$checkInUrl = url('lms/checkin');
	}
	$qrCode = SG\qrcode($checkInUrl,$qrCodeStyle);

	$ret .= '<div class="qrcode" style="margin: 16px auto; padding: 16px;">'
		. $qrCode.'<br />'
		. '<a class="btn -link" href="'.$checkInUrl.'">Check In</a><br />'
		. '</div>';
	*/

	//$ret .= print_o($courseInfo,'$courseInfo');

	$ret .= '<style type="text/css">
	.lms-checkin-teacher.-class-list .detail {flex: 1; background-color: #fafafa; border-right: 1px #eee solid;}
	.lms-checkin-teacher.-class-list .menu {flex: 0 0 35%; padding: 32px 16px;}
	.qrcode {text-align: center;}
	.qrcode>img {display: block; margin: 0 auto;}
	</style>';
	return $ret;
}
?>