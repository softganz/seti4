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

function lms_manage_navigator($self, $courseInfo) {
	R::View('toolbar', $self, 'Manage/'.$courseInfo->name, 'lms', $courseInfo);

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$self->theme->sidebar = R::View('lms.manage.menu', $courseInfo);

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

	return $ret;
}
?>