<?php
/**
* Project :: Report Check Information
* Created 2020-01-26
* Modify  2020-01-26
*
* @param Object $self
* @param Object $projectInfo
* @param Int $period
* @return String
*
* @usage project/{id}/info.check/{tranId}
*/

$debug = true;

function project_info_check($self, $projectInfo, $period) {
	$getChild = post('child');

	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'ขออภัย ท่านไม่สามารถใช้งานเมนูนี้ได้');

	$childInfo = R::Model('project.get', $getChild);

	if ($childInfo->info->parent != $projectInfo->projectId) return message('error', 'ขออภัย ไม่ใช่โครงการภายใต้ความรับผิดชอบของท่าน');

	// Create period
	//debugMsg(cfg('project')->follow->ownerType->graduate->budget);
	//mydb::query('DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "period"', ':tpid', $childInfo->projectId);

	/*
	$periodInfo = R::Model('project.period.get', $childInfo->projectId);
	if (in_array($childInfo->info->ownertype, array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE)) && !$periodInfo) {
		$periodInfo = R::Model('project.employee.period.create', $childInfo);
	}

	*/

	$periodInfo = R::Model('project.period.get', $childInfo->projectId, $period);
	//debugMsg($periodInfo, '$periodInfo');

	$ui = new Ui();
	$ui->addConfig('container', '{tag: "nav", class: "nav"}');
	$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info/send.checked/'.$period, array('child' => $getChild, 'remove'=>'Yes')).'" data-rel="none" data-done="close | load" data-title="ลบการตรวจรายงาน" data-confirm="ลบการตรวจรายงาน กรุณายืนยัน?"><i class="icon -material">delete</i></a>');
	$ret .= '<header class="header"><h3>Monthly Report Check</h3>'.$ui->build().'</header>';

	$form = new Form(NULL, url('project/'.$projectId.'/info/send.checked/'.$period, array('child' => $getChild)), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addField(
		'money',
		array(
			'type' => 'text',
			'label' => 'อนุมัตให้เบิกเงินค่าจ้าง (บาท)',
			'class' => '-money',
			'require' => true,
			'value' => number_format(SG\getFirst($periodInfo->paidAmt, $periodInfo->budget),2),
			'placeholder' => '0.00',
		)
	);

	$form->addField(
		'confirm',
		array(
			'type' => 'checkbox',
			'require' => true,
			'options' => array('YES' => 'ยืนยันการตรวจสอบรายงาน'),
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

	/*
	$qtCondition = array('qtref' => $qtRef);
	$surveyInfo = R::Model('qt.get', $qtCondition, '{debug: false}');

	if ($surveyInfo->info->qtform) {
		$formInfo = R::Model('qt.form.get', $surveyInfo->info->qtform);
		$schema = $formInfo->info->schema;
	}

	$ret .= print_o($surveyInfo, '$surveyInfo');
	$ret .= print_o($formInfo, '$formInfo');
	*/
	//$ret .= print_o($childInfo, '$childInfo');
	//$ret .= print_o($projectInfo, '$projectInfo');

	return $ret;
}
?>