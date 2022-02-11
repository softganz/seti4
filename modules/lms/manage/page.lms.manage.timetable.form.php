<?php
/**
* LMS : Change Top Navigator
* Created 2020-08-05
* Modify  2020-08-05
*
* @param Object $self
* @param Object $courseInfo
* @return String
*
* @usage lms/{$courseId}/manage.navigator
*/

$debug = true;

function lms_manage_timetable_form($self, $courseInfo, $classId = NULL) {
	// Data Model
	$getModule = post('mod');


	// View Model
	R::View('toolbar', $self, 'Manage/'.$courseInfo->name, 'lms', $courseInfo);

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$self->theme->sidebar = R::View('lms.manage.menu', $courseInfo);

	if ($classId) {
		$stmt = 'SELECT t.*, m.`name` `moduleName`
			FROM %lms_timetable% t
				LEFT JOIN %lms_mod% m ON m.`modid` = t.`modid`
			WHERE `classid` = :classId
			LIMIT 1';
		$classInfo = mydb::select($stmt, ':classId', $classId);
		$classInfo->timestart = sg_date($classInfo->start, 'H:i');
		$classInfo->timeend = sg_date($classInfo->end, 'H:i');
	}

	$moduleList = array();
	$stmt = 'SELECT c.`modid`, m.`name` FROM %lms_cmod% c LEFT JOIN %lms_mod% m USING(`modid`) WHERE c.`courseid` = :courseId';
	foreach (mydb::select($stmt, ':courseId', $courseId)->items as $rs) {
		$moduleList[$rs->modid] = $rs->name;
	}



	//$ret .= print_o($moduleList);


	$ret .= '<header class="header">'._HEADER_BACK.'<h3>'.($classId ? 'แก้ไข' : 'เพิ่ม').'ตารางเรียน</h3></header>';

	/*
	$tables = new Table();
	$tables->rows[] = array('กิจกรรม',$classInfo->title);
	$tables->rows[] = array('รายวิชา',$classInfo->moduleName);
	$tables->rows[] = array('เริ่มเวลา',$classInfo->start);
	$tables->rows[] = array('จบเวลา',$classInfo->end);
	$tables->rows[] = array('เช็คอินก่อน(นาที)',$classInfo->openbeforemin);
	$tables->rows[] = array('เช็คอินหลัง(นาที)',$classInfo->openaftermin);
	$tables->rows[] = array('ผู้สอน',$classInfo->teacherid);
	$tables->rows[] = array('ผู้ร่วมสอน',$classInfo->speaker);

	$ret .= $tables->build();

	$ret .= '<p>รายละเอียด</p>'.nl2br($classInfo->detail);
	*/

	$form = new Form([
		'variable' => 'data',
		'action' => url('lms/'.$courseId.'/manage.info/timetable.save/'.$classId),
		'class' => 'sg-form',
		'rel' => 'notify',
		'checkValid' => true,
		'done' => 'back | load:.box-page | load->replace:#lms-manage-timetable-list',
		'children' => [
			'title' => [
				'type' => 'text',
				'label' => 'ชื่อกิจกรรม',
				'class' => '-fill',
				'require' => true,
				'value' => $classInfo->title,
			],
			'modid' => [
				'type' => 'select',
				'label' => 'รายวิชา',
				'class' => '-fill',
				'options' => $moduleList,
				'value' => SG\getFirst($classInfo->modid, $getModule),
			],
			'serno' => [
				'type' => 'select',
				'label' => 'สำหรับผู้เรียนรุ่นที่:',
				'class' => '-fill',
				'options' => mydb::select('SELECT s.`serno` FROM %lms_course% c LEFT JOIN %lms_student% s USING(`courseid`) WHERE c.`courseid` = :courseId GROUP BY s.`serno` ORDER BY `serno` DESC; -- {key: "serno", value: "serno"}', ':courseId', $courseId)->items,
				'value' => $classInfo->serno,
			],
			'start' => [
				'type' => 'text',
				'label' => 'เริ่มเวลา',
				'class' => 'sg-datepicker -date',
				'require' => true,
				'value' => sg_date(SG\getFirst($classInfo->start, date('Y-m-d')),'d/m/Y'),
				'posttext' => '<span class="input-append"><span><input class="form-text -require" type="text" name="data[timestart]" value="'.$classInfo->timestart.'" placeholder="00:00"></span></span>',
				'container' => '{class: "group"}',
			],
			'end' => [
				'type' => 'text',
				'label' => 'จบเวลา',
				'class' => 'sg-datepicker -date',
				'require' => true,
				'value' => sg_date(SG\getFirst($classInfo->end, date('Y-m-d')),'d/m/Y'),
				'posttext' => '<span class="input-append"><span><input class="form-text -require" type="text" name="data[timeend]" value="'.$classInfo->timeend.'" placeholder="00:00"></span></span>',
				'container' => '{class: "group"}',
			],
			'openbeforemin' => [
				'type' => 'text',
				'label' => 'เช็คอินก่อน(นาที)',
				'class' => '-fill',
				'value' => $classInfo->openbeforemin,
			],
			'openaftermin' => [
				'type' => 'text',
				'label' => 'เช็คเอ้าท์หลัง(นาที)',
				'class' => '-fill',
				'value' => $classInfo->openaftermin,
			],
			'speaker' => [
				'type' => 'textarea',
				'label' => 'ผู้ร่วมสอน',
				'class' => '-fill',
				'rows' => 2,
				'value' => $classInfo->speaker,
			],
			'detail' => [
				'type' => 'textarea',
				'label' => 'รายละเอียด',
				'class' => '-fill',
				'rows' => 4,
				'value' => $classInfo->detail,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret .= $form->build();

	//$ret .= print_o($classInfo,'$classInfo');
	return $ret;
}
?>