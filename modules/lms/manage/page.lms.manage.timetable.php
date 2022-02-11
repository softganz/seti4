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

function lms_manage_timetable($self, $courseInfo, $classId = NULL, $moduleId = NULL) {
	R::View('toolbar', $self, 'Manage/'.$courseInfo->name, 'lms', $courseInfo);

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$self->theme->sidebar = R::View('lms.manage.menu', $courseInfo);

	if (empty($classId)) {
		$ret .= '<div id="lms-manage-timetable-list" data-url="'.url('lms/'.$courseId.'/manage.timetable').'">';
		mydb::where('t.`courseid` = :courseId', ':courseId', $courseId);
		if ($moduleId) mydb::where('t.`modid` = :modid', ':modid', $moduleId);
		$stmt = 'SELECT t.*, m.`name` `moduleName`
			FROM %lms_timetable% t
				LEFT JOIN %lms_mod% m ON m.`modid` = t.`modid`
			%WHERE%
			ORDER BY `start`';
		$dbs = mydb::select($stmt);

		$tables = new Table();
		$tables->thead = array(
			'start -date' => 'วันที่',
			'time -amt' => 'เวลา',
			'checkin -amt' => '<i class="icon -material">login</i>',
			'title -hover-parent' => 'กิจกรรม',
		);
		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				sg_date($rs->start, 'd/m/ปปปป'),
				sg_date($rs->start, 'H:i'),
				$rs->openbeforemin > 0 ? $rs->openbeforemin : '',
				'<a class="sg-action" href="'.url('lms/'.$courseId.'/manage.timetable/'.$rs->classid).'" data-rel="box" data-width="640" data-height="90%">'.$rs->title.'</a><br /><em>'.$rs->moduleName.'</em>',
			);
		}

		$ret .= $tables->build();

		$ret .= R::View('button.floating',
			array(
				'<a class="sg-action btn -floating -circle48" href="'.url('lms/'.$courseId.'/manage.timetable.form', array('mod' => $moduleId)).'" data-rel="box" data-width="640" data-height="90%" title="เพิ่มตารางเรียน"><i class="icon -material">add</i></a>'
			)
		);
		$ret .= '</div>';

		return $ret;
	}




	$stmt = 'SELECT t.*, m.`name` `moduleName`
		FROM %lms_timetable% t
			LEFT JOIN %lms_mod% m ON m.`modid` = t.`modid`
		WHERE `classid` = :classId
		LIMIT 1';

	$classInfo = mydb::select($stmt, ':classId', $classId);


	$headerNav = new Ui();
	$headerNav->addConfig('nav', '{class: "nav"}');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('lms/'.$courseId.'/manage.timetable.form/'.$classInfo->classid).'" data-rel="box"><i class="icon -material">edit</i></a>');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('lms/'.$courseId.'/manage.info/timetable.delete/'.$classInfo->classid).'" data-rel="notify" data-done="close | load->replace:#lms-manage-timetable-list" data-title="ลบตารางเรียน" data-confirm="ต้องการลบตารางเรียน กรุณายืนยัน?"><i class="icon -material">delete</i></a>');

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>'.$classInfo->title.'</h3>'.$headerNav->build().'</header>';

	$tables = new Table();
	$tables->rows[] = array('กิจกรรม',$classInfo->title);
	$tables->rows[] = array('รายวิชา',$classInfo->moduleName);
	$tables->rows[] = array('เริ่มเวลา',sg_date($classInfo->start,'d-m-ปปปป H:i'));
	$tables->rows[] = array('จบเวลา',sg_date($classInfo->end,'d-m-ปปปป H:i'));
	$tables->rows[] = array('เช็คอินก่อน(นาที)',$classInfo->openbeforemin);
	$tables->rows[] = array('เช็คเอ้าท์หลัง(นาที)',$classInfo->openaftermin);
	$tables->rows[] = array('ผู้สอน',$classInfo->teacherid);
	$tables->rows[] = array('ผู้ร่วมสอน',$classInfo->speaker);

	$ret .= $tables->build();

	$ret .= '<p>รายละเอียด</p>'.nl2br($classInfo->detail);

	//$ret .= print_o($classInfo,'$classInfo');

	/*
	$form = new Form(NULL, url('lms/'.$courseId.'/manage.info/navigator.save'), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'reload');

	$form->addField(
		'navigator',
		array(
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 24,
			'value' => cfg('navigator.lms.'.$courseId),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();
	*/

	return $ret;
}
?>