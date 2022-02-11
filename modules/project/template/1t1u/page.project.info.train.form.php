<?php
/**
* Project :: Trainning Form
* Created 2021-02-08
* Modify  2021-02-08
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/{id}/info.train.form
*/

$debug = true;

function project_info_train_form($self, $projectInfo, $tranId = NULL) {
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isRight = is_admin('project') || $projectInfo->right->isOwner;

	if (!$isRight) return message('error', 'ขออภัย ท่านไม่สามารถใช้งานเมนูนี้ได้');

	$data = NULL;

	if (!$tranId) {
		$data->actionId = post('actionId');
		$stmt = 'SELECT `trid` FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = "info" AND `part` = "train" AND `parent` = :actionId LIMIT 1';
		$tranId = $data->trid = mydb::select($stmt, ':projectId', $projectId, ':actionId', $data->actionId)->trid;
	}

	if ($tranId) {
		$stmt = 'SELECT
			  `trid`
			, `tpid`
			, `parent` `actionId`
			, `refid` `trainingType`
			, `date1` `trainingDate`
			, `detail1` `trainingLoc`
			, CAST(`num1` AS UNSIGNED) `trainingHour`
			, `text1` `trainingDetail`
			, `text2` `learnDetail`
			FROM %project_tr%
			WHERE `trid` = :trid
			LIMIT 1';
		$data = mydb::select($stmt, ':trid', $tranId);
	}
	//$ret .= print_o($data,'$data');

	$form = new Form('data', url('project/'.$projectId.'/info/train.save/'.$tranId), NULL, 'sg-form');
	$form->header(_HEADER_BACK.'<h3>รายงานการฝึกอบรม</h3>');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load:#project-action-'.$data->actionId);

	$form->addField('actionId', array('type' => 'hidden', 'value' => $data->actionId));

	$form->addField(
		'trainingType',
		array(
			'type' => 'radio',
			'label' => 'การฝึกอบรมทักษะในด้าน:',
			'require' => true,
			'options' => array(
				1 => 'Digital Literacy',
				2 => 'English Competency', 
				3 => 'Financial Literacy',
				4 => 'Social Literacy',
				99 => 'อื่นๆ',
			),
			'value' => $data->trainingType,
		)
	);

	$form->addField(
		'trainingLoc',
		array(
			'type' => 'text',
			'label' => 'สถานที่ฝึกอบรม',
			'class' => '-fill',
			'value' => htmlspecialchars($data->trainingLoc),
			'placeholder' => 'ระบุสถานที่ฝึกอบรม',
		)
	);

	$form->addField(
		'trainingDate',
		array(
			'type' => 'text',
			'label' => 'วันที่ฝึกอบรม',
			'class' => 'sg-datepicker',
			'require' => true,
			'readonly' => true,
			'value' => sg_date(SG\getFirst($data->trainingDate, date('d/m/Y')), 'd/m/Y'),
			'placeholder' => 'DD/MM/YYYY',
		)
	);

	$form->addField(
		'trainingHour',
		array(
			'type' => 'select',
			'label' => 'จำนวนชั่วโมงฝึกอบรม:',
			'options' => '1..72',
			'value' => $data->trainingHour,
		)
	);

	$form->addField(
		'trainingDetail',
		array(
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->trainingDetail,
			'placeholder' => 'ระบุรายละเอียดการฝึกอบรมทักษะ',
		)
	);

	/*
	$form->addField(
		'learnType',
		array(
			'type' => 'radio',
			'label' => 'สิ่งที่ได้เรียนรู้เพิ่มเติมในด้าน:',
			'options' => array(
				1 => 'Digital Literacy',
				2 => 'English Competency', 
				3 => 'Financial Literacy',
				4 => 'Social Literacy',
			),
		)
	);
	*/

	$form->addField(
		'learnDetail',
		array(
			'type' => 'textarea',
			'label' => 'สิ่งที่ได้เรียนรู้',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->learnDetail,
			'placeholder' => 'ระบุรายละเอียดสิ่งที่ได้เรียนรู้',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>