<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_care_plan_edit($self, $psnId, $planId) {
	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{data: "info"}');
	$psnId = $psnInfo->psnId;

	if (!$psnId) return message('error','ไม่มีข้อมูลของผู้ป่วยที่ระบุ');

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) {
		$ret .= message('error',$psnInfo->error);
		return $ret;
	}

	$careInfo = R::Model('imed.care.get', $planId);

	$form = new Form('data', url('imed/care/'.$psnId.'/plan.save/'.$planId), NULL, 'sg-form');

	$form->addData('rel', '#imed-app');
	$form->addData('ret', url('imed/care/'.$psnId.'/plan/'.$planId, array('org' => $careInfo->orgid)));

	$form->addField(
					'datemake',
					array(
						'type' => 'text',
						'label' => 'วันที่จัดทำ',
						'class' => 'sg-datepicker',
						'require' => true,
						'readonly' => true,
						'value' => sg_date($careInfo->info->datemake, 'd/m/Y'),
						'placeholder' => '31/12/2560',
					)
				);

	$form->addField(
					'diagnose',
					array(
						'type' => 'text',
						'label' => 'การวินิจฉัยโรค',
						'class' => '-fill',
						'value' => htmlspecialchars($careInfo->info->diagnose),
						'placeholder' => 'ระบุการวินิจฉัยโรค',
					)
				);

	$form->addField(
					'tai',
					array(
						'type' => 'text',
						'label' => 'TAI',
						'class' => '',
						'value' => htmlspecialchars($careInfo->info->tai),
						'placeholder' => '',
					)
				);

	$form->addField(
					'adl',
					array(
						'type' => 'text',
						'label' => 'ADL',
						'class' => '',
						'value' => htmlspecialchars($careInfo->info->adl),
						'placeholder' => '',
					)
				);

	$form->addField(
					'conceptlive',
					array(
						'type' => 'textarea',
						'label' => 'แนวคิดของผู้รับบริการและครอบครัวที่มีต่อการดำรงชีวิต',
						'class' => '-fill',
						'rows' => 2,
						'value' => htmlspecialchars($careInfo->info->conceptlive),
						'placeholder' => '',
					)
				);


	$form->addField(
					'conceptlong',
					array(
						'type' => 'textarea',
						'label' => 'แนวนโยบายการให้ความช่วยเหลือโดยรวม (รวมถึงเป้าหมายระยะยาว)',
						'class' => '-fill',
						'rows' => 2,
						'value' => htmlspecialchars($careInfo->info->conceptlong),
						'placeholder' => '',
					)
				);

	$form->addField(
					'caution',
					array(
						'type' => 'textarea',
						'label' => 'ข้อควรระวัง',
						'class' => '-fill',
						'rows' => 2,
						'value' => htmlspecialchars($careInfo->info->caution),
						'placeholder' => '',
					)
				);

	$form->addField(
					'problem',
					array(
						'type' => 'textarea',
						'label' => 'ประเด็นปัญหาในการดำรงชีวิต (ความต้องการ)',
						'class' => '-fill',
						'rows' => 2,
						'value' => htmlspecialchars($careInfo->info->problem),
						'placeholder' => '',
					)
				);

	$form->addField(
					'targetshort',
					array(
						'type' => 'textarea',
						'label' => 'เป้าหมายการดำรงชีวิต (เป้าหมายระยะสั้น)',
						'class' => '-fill',
						'rows' => 2,
						'value' => htmlspecialchars($careInfo->info->targetshort),
						'placeholder' => '',
					)
				);

	$form->addField(
					'servicewant',
					array(
						'type' => 'textarea',
						'label' => 'บริการที่นอกเหนือจากรายสัปดาห์ (รวมการช่วยเหลือของครอบครัวและ informal)',
						'class' => '-fill',
						'rows' => 2,
						'value' => htmlspecialchars($careInfo->info->servicewant),
						'placeholder' => '',
					)
				);

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('imed/care/'.$psnId.'/plan/'.$careInfo->cpid).'" data-rel="#imed-app" data-done="moveto:0,0"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => '{class: "-sg-text-right"}',
					)
				);

	$ret .= $form->build();
	//$ret .= print_o($psnInfo, '$psnInfo');
	//$ret .= print_o($careInfo, '$careInfo');

	return $ret;
}
?>