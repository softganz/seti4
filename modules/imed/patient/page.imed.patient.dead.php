<?php
/**
* iMed :: Patient Dead
* Created 2020-12-21
* Modify  2020-12-21
*
* @param Object $self
* @param Object $psnInfo
* @return String
*
* @usage imed/patient/{id}/dead
*/

$debug = true;

function imed_patient_dead($self, $psnInfo) {
	if (!($psnId = $psnInfo->psnId)) return message('error', 'PROCESS ERROR');

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>บันทึกการเสียชีวิต</h3></header>';

	// View Model
	$form = new Form(NULL, url('imed/patient/'.$psnId.'/info/dead'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | reload');

	$form->addField(
		'date',
		array(
			'type' => 'text',
			'label' => 'วันที่เสียชีวิต',
			'class' => 'sg-datepicker',
			'require' => true,
			'readonly' => true,
			'value' => $psnInfo->info->ddisch ? sg_date($psnInfo->info->ddisch, 'd/m/Y') : '',
			'placeholder' => '31/12/2500',
		)
	);

	$form->addField(
		'dead',
		array(
			'type' => 'text',
			'label' => 'สาเหตุการเสียชีวิต',
			'class' => '-fill',
			'value' => htmlspecialchars($psnInfo->person->dead->course),
			'placeholder' => 'ระบุสาเหตุการเสียชีวิต',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret .= $psnInfo->personJSON;
	//$ret .= print_o(SG\json_decode($psnInfo->personJSON),'aaa');
	//$ret .= print_o($psnInfo->person, '$psnInfo->person');

	//$ret .= print_o($psnInfo, '$psnInfo');
	/*
	 data-height="240" data-title="บันทึกการเสียชีวิต" data-confirm="ผู้ป่วยรายนี้ได้เสียชีวิตแล้ว กรุณายืนยัน?" data-done="reload" data-options=\'{"silent": true}\'
	 */
	return $ret;
}
?>