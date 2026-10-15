<?php
/**
* Module :: Description
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

function admin_category_change($self, $tagInfo) {
	// Data Model
	list($module) = explode(':', $tagInfo->taggroup);

	// View Model
	$ret = '<header class="header">'._HEADER_BACK.'<h3>เปลี่ยนรหัสค่าใช้จ่าย</h3></header>';

	$form = new Form(NULL, url($module.'/admin/category/'.$tagInfo->tid.'/change'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load');

	$form->addField('taggroup', array('type' => 'hidden', 'value' => $tagInfo->taggroup));

	$form->addField(
		'to',
		array(
			'type' => 'text',
			'label' => 'รหัสใหม่',
			'require' => true,
			'placeholder' => 'Ex. 1001',
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

	//$ret .= print_o($tagInfo,'$tagInfo');
	return $ret;
}
?>