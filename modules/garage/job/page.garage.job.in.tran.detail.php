<?php
/**
* Garage :: Job Car In Transaction Detail
* Created 2020-06-17
* Modify  2020-06-17
*
* @param Object $self
* @param Object $jobInfo
* @return String
*/

$debug = true;

function garage_job_in_tran_detail($self, $jobInfo, $tranId) {
	if (!($jobId = $jobInfo->tpid)) return message('error', 'NO JOB');

	$ret = '<header class="header">'._HEADER_BACK.'<h3>รายการซ่อม</h3></header>';

	if (array_key_exists($tranId, $jobInfo->command)) {
		$data = $jobInfo->command[$tranId];
	} else if (array_key_exists($tranId, $jobInfo->part)) {
		$data = $jobInfo->part[$tranId];
	}
	if (!$data) return message('error');

	$damagecodeList = mydb::select('SELECT * FROM %garage_damage%')->items;

	//$damagecodeOptions = array('' => '???');
	//foreach ($damagecodeList as $v) {
		//$damagecodeOptions[$v->damagecode] = $v->damagecode.' : '.$v->damagename;
		//'<option value="'.$v->damagecode.'" '.($jobTranInfo->damagecode == $v->damagecode?'selected="selected"':'').' data-pretext="'.$v->pretext.'">'.$v->damagecode.' : '.$v->damagename.'</option>';
	//}

	$damagecodeOptions = '<option value="">???</option>';
	foreach ($damagecodeList as $v) {
		$damagecodeOptions .= '<option value="'.$v->damagecode.'" '.($data->damagecode == $v->damagecode?'selected="selected"':'').' data-pretext="'.$v->pretext.'">'.$v->damagecode.' : '.$v->damagename.'</option>';
	}


	$form = new Form(NULL, url('garage/job/'.$jobId.'/info/tran.save/'.$tranId), NULL, 'sg-form -in-detail');
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load->replace:.garage-in-form:'.url('garage/job/'.$jobId.'/in.tran'));

	$form->addField('datecmd', array('type' => 'hidden', 'value' => $data->datecmd));
	$form->addField('repairid', array('type' => 'hidden', 'value' => $data->repairid));
	$form->addField('price', array('type' => 'hidden', 'value' => $data->price));
	$form->addField('vatrate', array('type' => 'hidden', 'value' => $data->vatrate));
	$form->addField('vatamt', array('type' => 'hidden', 'value' => $data->vatamt));
	$form->addField('totalsale', array('type' => 'hidden', 'value' => $data->totalsale));

	$form->addField(
		'description',
		array(
			'type' => 'text',
			'label' => 'รายการ',
			'class' => '-fill',
			'value' => htmlspecialchars($data->repairname),
		)
	);

	$form->addField(
		'qty',
		array(
			'type' => 'select',
			'label' => 'จำนวน:',
			'class' => '-fill',
			'options' => '1..20',
			'value' => $data->qty,
		)
	);

	if ($data->repairtype == 1) {
		$form->addField(
			'damagecode',
			array(
				'type' => 'select',
				'label' => 'ระดับความเสียหาย:',
				'class' => '-fill',
				'value' => $data->damagecode,
				'options' => $damagecodeOptions,
			)
		);
	}

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('garage/job/'.$jobId.'/in').'" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($data,'$data');
	//$ret .= print_o($jobInfo,'$jobInfo');

	return $ret;
}
?>