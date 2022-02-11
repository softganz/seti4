<?php
/**
* LMS :: Course Evaluation
* Created 2020-07-01
* Modify  2020-07-01
*
* @param Object $self
* @param Object $courseInfo
* @return String
*/

$debug = true;

function lms_student_survey($self) {
	R::View('toolbar', $self, 'ประเมินผลหลักสูตร', 'lms', $courseInfo, '{searchform: false}');

	//if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$moduleId = SG\getFirst($moduleId, post('mod'));

	$isAdmin = user_access('administer lms');
	$currentDate = date('Y-m-d H:i:s');

	$inCourse = R::Model('lms.course.student', array('uid'=>i()->uid), '{debug: false}');

	$isEvalDate = $courseInfo->info->datebegin && $courseInfo->info->dateend && $currentDate >= $courseInfo->info->datebegin && $currentDate <= $courseInfo->info->dateend;


	if (!i()->ok) {
		head('<style tyle="text/css">
	.lms-mod-survey .detail {flex: 1; background-color: #fafafa; border-right: 1px #eee solid;}
	.lms-mod-survey .menu {flex: 0 0 35%; padding: 32px 16px;}

	.login, #cboxContent .login {width: 280px; margin: 0 auto; background-color: transparent; border: none;}
	.login .-info {display: none;}
	.login .-form>h3 {display: none;}
	.login .-form>h5 {display: none;}
	.login .-form>ul {display: none;}
	.login .-form {width: 100%; margin: 0;}
	.login .-form .signform>.ui-action>a:first-child {display: none;}

	</style>');
		//$ret .= R::View('signform', '{time:-1, rel: "none", done: "load | load->clear:box:'.url('lms/checkin').'"}');
		//return R::View('signform', '{time:-1, class: "signform sg-form", rel: "none", done: "load:#main:'.url('lms/student/survey').'"}');
		return R::View('signform', '{time:-1}');
	}


	//$ret = '<header class="header"><h3>เลือกรายวิชาที่ต้องการประเมิน:</h3></header>';

	// ทุกแบบสอบถามที่ทำแล้ว ของ นักศึกษา รวมทั้งแบบสอบถามที่ยังไม่ได้ทำ
	//mydb::where(':currentDate BETWEEN su.`datestart` AND su.`dateend`', ':currentDate', date('Y-m-d H:i:s'));
	mydb::where('s.`uid` = :uid', ':uid', i()->uid, ':currentDate', date('Y-m-d H:i:s'));

	$stmt = 'SELECT
		su.*
		, s.`sid`, s.`uid`
		, IF(:currentDate BETWEEN su.`datestart` AND su.`dateend`, 1, 0) `surveyActive`
		, q.`qtref`
		, c.`name` `courseName`
		, m.`name` `moduleName`
		FROM %lms_survey% su
			LEFT JOIN %lms_course% c USING(`courseid`)
			LEFT JOIN %lms_mod% m USING(`modid`)
			LEFT JOIN %lms_student% s ON s.`courseid` = su.`courseid` AND s.`uid` = :uid
			LEFT JOIN %qtmast% q ON q.`lmssurid` = su.`surid` AND q.`uid` = :uid
		%WHERE%
		GROUP BY `surid`
		';

	$dbs = mydb::select($stmt);

	$cardUi = new Ui('div', 'ui-card lms-mod-survey -active');
	$cardUiInactive = new Ui('div', 'ui-card lms-mod-survey -inactive');

	foreach ($dbs->items as $rs) {
		if (empty($rs->formid)) continue;

		$menuLink = '';
		$cardNav = new Ui();

		if ($rs->surveyActive) {
			if ($rs->qtref) {
				$menuLink = '<a class="btn" href="'.url('lms/survey/'.$rs->qtref).'"><i class="icon -material">edit</i><span>แก้ไข</span></a>';
			} else {
				$menuLink = '<a class="sg-action btn -primary" href="'.url('lms/'.$rs->courseid.'/info/survey.create/'.$rs->surid).'" data-title="ทำแบบประเมิน" data-confirm="ต้องการทำแบบประเมิน กรุณายืนยัน?"><i class="icon -material">add</i><span>ทำแบบประเมิน</span></a>';				
			}
			$cardUi->add(
				'<div class="detail">'
				. '<b>'.$rs->title.'</b><br />รายวิชา '.$rs->moduleName.'<br />หลักสูตร '.$rs->courseName
				. '</div>'
				. '<div class="menu">'
				. '<nav class="nav -card -sg-text-center">'.$menuLink.'</nav>'
				. '</div>',
				'{class: "-sg-flex -sg-text-center"}'
			);
		} else {
			if ($rs->qtref) {
				$menuLink = '<a class="btn"><i class="icon -material">done_all</i><span>เรียบร้อย</span></a>';
			}
			$cardUiInactive->add(
				'<div class="detail">'
				. '<b>'.$rs->title.'</b><br />รายวิชา '.$rs->moduleName.'<br />หลักสูตร '.$rs->courseName
				. '</div>'
				. '<div class="menu">'
				. '<nav class="nav -card -sg-text-center">'.$menuLink.'</nav>'
				. '</div>',
				'{class: "-sg-flex -sg-text-center"}'
			);
		}
	}

	if ($cardUi->count()) {
		$ret .= $cardUi->build();
	} else {
		$ret .= '<p class="notify">ไม่มีแบบสอบถามที่ท่านต้องดำเนินการในช่วงเวลานี้</p>';
	}


	if ($cardUiInactive->count()) {
		$ret .= '<h3>แบบสอบถามไม่อยู่ในช่วงเวลา</h3>';

		$ret .= $cardUiInactive->build();
	}

	//$ret .= print_o($dbs);

	$ret .= '<style tyle="text/css">
	.lms-mod-survey {flex-wrap: wrap;}
	.lms-mod-survey .ui-item>.detail {flex: 0 0 100%; margin: 0; padding: 8px 0; background-color: #fafafa;}
	.lms-mod-survey .menu {flex: 0 0 100%; margin: 0; padding: 32px 0;}

	.login, #cboxContent .login {width: 280px; margin: 0 auto; background-color: transparent; border: none;}
	.login .-info {display: none;}
	.login .-form>h3 {display: none;}
	.login .-form>h5 {display: none;}
	.login .-form>ul {display: none;}
	.login .-form {width: 100%; margin: 0;}
	.login .-form .signform>.ui-action>a:first-child {display: none;}

	@media (min-width:30em){    /* 480/16 */
		.lms-mod-survey {flex-wrap: no-wrap;}
		.lms-mod-survey .ui-item>.detail {flex: 1; border-right: 1px #eee solid;}
		.lms-mod-survey .menu {flex: 0 0 35%; padding: 32px 16px;}
	}
	</style>';

	return $ret;
}
?>