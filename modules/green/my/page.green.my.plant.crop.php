<?php
/**
* Green : Crop Plant
* Created 2020-11-14
* Modify  2020-11-14
*
* @param Object $self
* @param Int $plantId
* @return String
*
* @usage green/my/plant/{$Id}/crop
*/

$debug = true;

function green_my_plant_crop($self, $plantId) {
	$plantInfo = R::Model('green.plant.get', $plantId, '{data: "orgInfo"}');

	if (!$plantInfo) return 'ไม่มีรายการ';

	$orgInfo = $plantInfo->orgInfo;

	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;
	$isItemEdit = $isEdit || $plantInfo->uid == i()->uid;

	$ret = '<header class="header">'._HEADER_BACK.'<h3>เก็บเกี่ยว</h3></header>';

	$form = new Form(NULL, url('green/my/info/plant.crop/'.$plantId), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'back');

	$form->addField(
		'croped',
		array(
			'label' => 'วันที่เก็บเกี่ยว',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'readonly' => true,
			'require' => true,
			'value' => $plantInfo->info->croped ? sg_date($plantInfo->info->croped, 'd/m/Y') : date('d/m/Y'),
			'placeholder' => '31/12/2562',
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