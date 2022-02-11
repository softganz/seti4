<?php
/**
* LMS : Course Homepage
* Created 2020-08-06
* Modify  2020-08-06
*
* @param Object $self
* @param Object $courseInfo
* @return String
*
* @usage lms/{$courseId}/manage.homepage
*/

$debug = true;

function lms_manage_homepage($self, $courseInfo) {
	R::View('toolbar', $self, 'Manage/'.$courseInfo->name, 'lms', $courseInfo);

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$self->theme->sidebar = R::View('lms.manage.menu', $courseInfo);

	$homePageInfo = R::Model('lms.course.homepage.get', $courseId, '{debug: false}');


	$form = new Form(NULL, url('lms/'.$courseId.'/manage.info/homepage.save'), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	//$form->addData('done', 'reload');

	$form->addField(
		'html',
		array(
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 24,
			'value' => $homePageInfo->html,
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