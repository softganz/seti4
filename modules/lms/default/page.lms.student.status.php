<?php
/**
* LMS :: View Student Information
* Created 2020-07-10
* Modify  2020-07-10
*
* @param Object $self
* @param Object $studentInfo
* @return String
*/

$debug = true;

function lms_student_status($self, $studentInfo) {
	R::View('toolbar', $self, 'นักศึกษา/'.$studentInfo->name, 'lms', $studentInfo, '{searchform: false}');

	if (!($studentId = $studentInfo->studentId)) return message('error', 'PROCESS ERROR');

	$isAdmin = user_access('administer lms');
	$isTeacher = user_access('teacher lms');

	$isEditable = $isAdmin || $isTeacher;

	if (!$isEditable) return message('error', 'Access Denied');

	$statusList = array('Graduate','Active','First Probation','Second Probation','Third Probation','Retired','Quit');

	$ret = '<header class="header">'._HEADER_BACK.'<h3>'.$studentInfo->name.'</h3></header>';;

	$statusOption = Array();
	foreach ($statusList as $key => $value) $statusOption[$value] = '<i class="icon -material">check_circle</i>'.$value;

	$form = new Form(NULL, url('lms/'.$studentInfo->courseId.'/info/student.status/'.$studentId), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load: .box-page');

	$form->addField(
		'status',
		array(
			'type' => 'radio',
			'class' => '-hidden',
			'options' => $statusOption,
			'value' => $studentInfo->info->status,
			'config' => '{capsule: {tag: "abbr", class: "checkbox"}}',
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

	//$ret .= print_o($studentInfo, '$studentInfo');

	$ret .= '<style tyle="text/css">
	.detail>section {padding: 16px; border-bottom: 1px #eee solid;}
	.detail>section:first-child {border-top: 1px #eee solid;}
	</style>';

	return $ret;
}
?>