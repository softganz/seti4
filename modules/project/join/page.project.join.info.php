<?php
/**
* Project Action Join Info
* Created 2019-02-28
* Modify  2019-07-30
*
* @param Object $self
* @param Int/Object $tpid
* @param Int/Object $calId
* @return String
*/

$debug = true;

function project_join_info($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	R::View('project.toolbar', $self, $projectInfo->calendarInfo->title, 'join', $projectInfo);


	$showJoinGroup = post('group');

	$isMember = $projectInfo->info->membershipType;
	$isOwner = $projectInfo->info->membershipType == 'OWNER';
	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($projectInfo->RIGHT & _IS_EDITABLE) || $isMember;
	$isAuthRefCode = $_SESSION['auth.join.refcode'];

	$registerBy = 'ลงทะเบียนร่วมกิจกรรม';
	if ($isAdmin || $isOwner) {
		$isRegister = true;
		$registerBy = 'ลงทะเบียนโดยตัวแทนเครือข่าย';
	} else if ($projectInfo->doingInfo->isregister == 1) {
		$isRegister = $isOwner && (date('U') >= $projectInfo->doingInfo->registstart && date('U') <= $projectInfo->doingInfo->registend);
		$registerBy = 'ลงทะเบียนโดยตัวแทนเครือข่าย';
	} else if ($projectInfo->doingInfo->isregister == 2) {
		$isRegister = $isMember && (date('U') >= $projectInfo->doingInfo->registstart && date('U') <= $projectInfo->doingInfo->registend);
		$registerBy = 'ลงทะเบียนโดยตัวแทนเครือข่าย';
	} else if ($projectInfo->doingInfo->isregister == 8) {
		$isRegister = i()->ok && (date('U') >= $projectInfo->doingInfo->registstart && date('U') <= $projectInfo->doingInfo->registend);
	} else if ($projectInfo->doingInfo->isregister == 9) {
		$isRegister = true;
	} else {
		$isRegister = false;
	}



	// Show Button for Register
	$ret .= '<div class="container -sg-clearfix"><div class="row">';
	$ret .= '<section class="col -md-4" style="height:300px; overflow: auto;">';
	$ret .= '<h3>'.$projectInfo->title.'</h3>';
	if ($projectInfo->calendarInfo->detail)
		$ret .= sg_text2html($projectInfo->calendarInfo->detail);
	$ret .= '</section>';




	$ret .= '<section class="qrcode -sg-text-center col -md-8">';
	//$ret .= '<h3>Register QR Code</h3>';
	/*
	$linkUrl = url('project/join/'.$tpid.'/'.$calId.'/register');
	$qrcodeUrl=_DOMAIN.urlencode($linkUrl);

	$ret .= '<img class="qrcode" src="https://chart.googleapis.com/chart?cht=qr&chl='.$qrcodeUrl.'&chs=160x160&choe=UTF-8&chld=L|2" alt="" style="display: block; margin:0 auto;"><br />';
	$ret .= '<a class="btn -primary" href="'.url('project/join/'.$tpid.'/'.$calId.'/register').'"><i class="icon -person-add -white"></i><span>สมัครเข้าร่วมงาน<br />( สำหรับบุคคลทั่วไป )</span></a><br /><br />';
	*/

	$linkUrl = url('project/join/'.$tpid.'/'.$calId);
	$qrcodeUrl=_DOMAIN.urlencode($linkUrl);

	//$ret .= '<img class="qrcode" src="https://chart.googleapis.com/chart?cht=qr&chl='.$qrcodeUrl.'&chs=160x160&choe=UTF-8&chld=L|2" alt="" style="display: block; margin:0 auto;"><br />';

	$ret .= '<nav class="nav -register">';
	if ($isRegister)
		$ret .= '<a class="btn -success -big" href="'.url('project/join/'.$tpid.'/'.$calId.'/invite').'" style=""><i class="icon -material -sg-48" style="display: block; margin:0 auto;">person_add</i><span style="color:#000;">'.$registerBy.'<br />'.($projectInfo->doingInfo->registend ? '(หมดเขต '.sg_date($projectInfo->doingInfo->registend,'ว ดดด ปปปป').')' : '').'</span></a><br /><br />';

	$ret .= '<a class="btn -edit-register" href="'.url('project/join/'.$tpid.'/'.$calId.'/list', array('group' => 'my')).'">แก้ไขข้อมูลการลงทะเบียน</a><br /><br />';

	$ret .= '<a class="btn -check-register" href="'.url('project/join/'.$tpid.'/'.$calId.'/checkname').'">ตรวจสอบรายชื่อผู้ลงทะเบียน</a><br /><br />';

	//$ret .= '<a class="btn -success" href="'.url('project/join/'.$tpid.'/'.$calId.'/register').'"><i class="icon -person-add -white"></i><span>ลงทะเบียนล่วงหน้า<br />( สำหรับบุคคลทั่วไป/เครือข่าย )</span></a><br /><br />';

	//$ret .= '<a class="btn -primary" href="'.url('project/join/'.$tpid.'/'.$calId.'/invite').'"><i class="icon -person-add -white"></i><span>บันทึกชื่อผู้เข้าร่วมจากเครือข่าย<br />( สำหรับตัวแทนเครือข่าย )</span></a><br /><br />';

	$ret .='</nav>';

	//$ret .= _DOMAIN.$linkUrl;
	$ret .= '</section>';

	$ret .= '</div><!-- row --></div><!-- container -->';

	//$ret .= '<br clear="all" />';

	head('<style type="text/css">
	.ui-menu {width: auto; text-align: center;}
	.ui-menu>li {margin:0 0 32px 0;}
	.btn.-edit-register {color: #fffdfc; background-color: #ff8c45; box-shadow: none;}
	.btn.-check-register {color: #f5eeff; background-color: #9048ff; box-shadow: none;}
	.nav.-register {width: 260px; margin:32px auto;}
	.nav.-register>a {display: block; font-size:1.2em; padding:16px;}
	.nav.-register>a>span {margin:0; padding:0;}
	.sg-chart.-jointype {height: 400px;}
	</style>');

	/*
	$ui = new Ui(NULL, 'ui-menu');
	//$ui->add('<a class="btn -big -fill" href="'.url('project/join/'.$tpid.'/'.$calId.'/register').'"><i class="icon -person-add -gray"></i><span>สมัครเข้าร่วมงานสำหรับบุคคลทั่วไป</span></a>');
	//$ui->add('<a class="btn -big -fill" href="'.url('project/join/'.$tpid.'/'.$calId.'/walkin').'"><i class="icon -person-add -gray"></i><span>สมัครเข้าร่วมหน้างาน Walk In</span></a><br />(สำหรับผู้เข้าร่วมที่ไม่ได้สมัครมาก่อน)');
	$ui->add('<a class="btn -big -fill" href="'.url('project/join/'.$tpid.'/'.$calId.'/invite').'"><i class="icon -person-add -gray"></i><span>บันทึกชื่อผู้เข้าร่วมจากเครือข่าย</span></a><br />สำหรับตัวแทนเครือข่าย/ผู้จัดการระบบ เป็นผู้บันทึก');

	$ret .= $ui->build();
	*/


	// ดูรายการได้ทุกคน สมาชิกดูได้หมด ไม่เป็นสมาชิกดูได้เฉพาะ $isAuthRefCode ของตัวเอง

	if ($isAdmin || $isMember) {

	} else if (i()->ok || $_SESSION['auth.join.refcode']) {
		return $ret;
		//mydb::where('ds.`uid` = :uid', ':uid', i()->uid);
	} else {
		return $ret;
	}






	if (!post('group')) {
		$isMobileDevice = isMobileDevice();

		$ret .= '<style>
		@media (min-width:45em) {		/* 720/16 = 45 */
		.sg-chart.-jointype {width: 60%; float: left;}
		.item.-sum {width: 40%; float: left;}
		}
		</style>';
		$stmt = 'SELECT `joingroup`, COUNT(*) `amt`
			FROM %org_dos% ds
				LEFT JOIN %org_doings% do USING(`doid`)
			WHERE `tpid` = :tpid AND `calid` = :calid AND ds.`isjoin` >= 0
			GROUP BY `joingroup`
			ORDER BY `amt` DESC;
			-- {sum: "amt"}';
		$joinGroupDbs = mydb::select($stmt, ':tpid', $tpid, ':calid', $calId);

		$tables = new Table();
		$tables->addClass('-sum');
		$graphYear = new Table();
		$graphYear->addClass('-hidden');

		$tables->thead = array('เครือข่าย', 'amt -total-type' => 'จำนวนคน','amt -percent' => '%');
		foreach ($joinGroupDbs->items as $rs) {
			$joinGroupName = SG\getFirst($rs->joingroup, 'ไม่ระบุ');

			$tables->rows[] = array(
				$isEdit ? '<a href="'.url('project/join/'.$tpid.'/'.$calId.'/list', array('group'=>SG\getFirst($rs->joingroup,'n/a'))).'">'.$joinGroupName.'</a>' : $joinGroupName,
				number_format($rs->amt),
				number_format($rs->amt*100/$joinGroupDbs->sum->amt,2),
			);

			$graphYear->rows[] = array(
				'string:Year' => SG\getFirst($rs->joingroup, 'ไม่ระบุ'),
				'number:Budget' => $rs->amt
			);
		}
		$tables->tfoot[] = array('รวม', number_format($joinGroupDbs->sum->amt),'');
		$ret .= '<div id="chart-app" class="sg-chart -jointype" data-chart-type="pie" data-options=\'{"pieHole": 0.4'.($isMobileDevice ? ', "legend": "bottom"' : '').'}\'>'._NL.$graphYear->build().'</div>'._NL;

		$ret .= $tables->build();
		head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
	}


	//$ret .= R::Page('project.join.list', NULL, $projectInfo);

	//$ret .= print_o($projectInfo,'$projectInfo');

	return $ret;
}
?>