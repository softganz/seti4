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

function lms_course_survey($self, $courseInfo, $moduleId = NULL) {
	R::View('toolbar', $self, 'ประเมินผลหลักสูตร'.($courseInfo->name ? '/' : '').$courseInfo->name, 'lms', $courseInfo, '{searchform: false}');

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$moduleId = SG\getFirst($moduleId, post('mod'));

	$isAdmin = user_access('administer lms');
	$currentDate = date('Y-m-d H:i:s');

	$inCourse = R::Model('lms.course.student', array('uid'=>i()->uid), '{debug: false}');

	$isEvalDate = $courseInfo->info->datebegin && $courseInfo->info->dateend && $currentDate >= $courseInfo->info->datebegin && $currentDate <= $courseInfo->info->dateend;


	if (!i()->ok) {
		return R::View('signform', '{class: "signform sg-form", rel: "none", done: "load:#info:'.url('lms/'.$courseId.'/mod.survey').'"}');
	} else if (!$inCourse) {
		return message('notify', 'ขออภัย ท่านไม่ได้ลงทะเบียนเรียนหลักสูตรนี้');
	}


	//$ret = '<header class="header"><h3>เลือกรายวิชาที่ต้องการประเมิน:</h3></header>';

	$stmt = 'SELECT su.*, COUNT(q.`qtref`) `totalQt`
		FROM %lms_survey% su
			LEFT JOIN %qtmast% q ON q.`lmssurid` = su.`surid`
		WHERE su.`courseid` = :courseId
		GROUP BY `surid`
		';

	$dbs = mydb::select($stmt, ':courseId', $courseId, ':currentDate', date('Y-m-d H:i:s'));

	$tables = new Table();
	$tables->thead = array('แบบสอบถาม', 'total -amt' => 'จำนวน');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array($rs->title, $rs->totalQt);
	}

	$ret .= $tables->build();

	/*

	$stmt = 'SELECT * FROM %qtmast% WHERE `courseid` = :courseId AND `uid` = :uid ; -- {key: "modid"}';
	$mySurvey = mydb::select($stmt, ':courseId', $courseId, ':moduleId', $moduleId, ':uid', i()->uid)->items;


	$cardUi = new Ui('div', 'ui-card lms-mod-survey');

	foreach ($courseInfo->module as $rs) {
		if (empty($rs->frmid)) continue;
		$cardUi->add(
			'<div class="form-item"><abbr class="checkbox -block">'
			. '<label>'
			. '<input class="-hidden" type="radio" name="mod" value="'.$rs->modid.'" />'
			. '<i class="icon -material">check_circle</i>'
			. '<span>'
			. '<span class="course-no">'.(++$no).'</span>'
			. '<span>'
			. '<b>'.$rs->name.'</b><br />'.SG\getFirst($rs->enname,'&nbsp;')
			. '</span>'
			. '</span>'
			. '</abbr>'
			. '</div>',
			'{class: "'.(isset($mySurvey[$rs->modid]) ? '-active' : '').'"}'
		);
	}

	$ret .= '<form class="lms-mod-survey-form-start" action="'.url('lms/'.$courseId.'/info/mod.survey.create').'">'
		. $cardUi->build()
		. '<div class="form-item -sg-text-right"><a class="btn -link -cancel" href="'.url('lms/'.$courseId).'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a> <button id="lms-mod-survey-start" class="btn -primary -disabled"><i class="icon -material">keyboard_arrow_right</i><span>เริ่มประเมิน</span></button></div>'
		. '</form>';

	//$ret .= print_o($courseInfo, '$courseInfo');

	$ret .= '<style tyle="text/css">
	.lms-mod-survey .form-item {padding: 0;}
	.lms-mod-survey .ui-item.-active {background-color: #e2ffe2; border: 1px #71d471 solid;}
	.lms-mod-survey .checkbox>label>span {padding-left: 4em; display: block;}
	.lms-mod-survey .course-no {display: block; position: absolute; left: 4px; top: calc(50% - 1.5em); width: 1em; height: 1em; line-height: 1em; padding: 1em; background-color: #999; color: #fff; border-radius: 50%; text-align: center; pointer-events: none;}
	.lms-mod-survey input[type="radio"]:checked+.course-no {background-color: green;}
	</style>';

	$ret .= '<script type="text/javascript">
	$("input[name=mod]").click(function() {
		$("#lms-mod-survey-start").removeClass("-disabled")
	});
	</script>';
	*/

	return $ret;
}
?>