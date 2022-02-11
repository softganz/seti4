<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_care_plan_tran_done($self, $psnId, $planId, $data = NULL) {
	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{data: "info"}');
	$psnId = $psnInfo->psnId;

	if (!$psnId) return message('error','ไม่มีข้อมูลของผู้ป่วยที่ระบุ');

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) {
		$ret .= message('error',$psnInfo->error);
		return $ret;
	}

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="close"><i class="icon -material">arrow_back</i></a></nav><h3>บันทึกการทำงาน</h3></header>';

	if (empty($data->donedate)) $data->donedate = $data->plandate.' '.$data->plantime.':00';
	//$data->donetime = SG\getFirst($data->donetime, $data->plantime);
	$data->doneDetail = SG\getFirst($data->doneDetail, $data->detail);

	$form = new Form('data', url('imed/care/'.$psnId.'/plan.tran.done/'.$planId), NULL, 'sg-form');
	$form->addData('checkValid',true);
	$form->addData('rel', 'replace:#imed-care-plan-tran');
	$form->addData('complete','closebox');
	//$form->addData('ret', url('imed/care/'.$psnId.'/plan.tran/'.$planId));

	$form->addField('tr', array('type'=>'hidden', 'name'=>'tr', 'value'=>$data->cptrid));
	$form->addField('seq', array('type'=>'hidden', 'value'=>$data->seq));
	$form->addField(
					'donedate',
					array(
						'type' => 'text',
						'label' => 'วันที่ดำเนินการ',
						'class' => 'sg-datepicker -fill',
						'require' => true,
						'readonly' => true,
						'value' => sg_date($data->donedate, 'd/m/Y'),
						'placeholder' => '31/12/2560',
					)
				);

	$form->addField(
					'donetime',
					array(
						'type' => 'time',
						'label' => 'เวลา',
						'class' => '-fill',
						'require' => true,
						'readonly' => true,
						'start' => 8,
						'end' => 21,
						'step' => 60,
						'value' => sg_date($data->donedate,'H:i'),
						'placeholder' => '31/12/2560',
					)
				);

	$form->addField(
					'doneDetail',
					array(
						'type' => 'textarea',
						'label' => 'รายละเอียดการทำงาน',
						'class' => '-fill',
						'rows' => 6,
						'value' => $data->doneDetail,
						'placeholder' => 'อธิบายรายละเอียดการทำงาน',
					)
				);

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('imed/care/'.$psnId.'/plan/'.$planId).'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => '{class: "-sg-text-right"}',
					)
				);
	$ret .= $form->build();

	//$ret .= print_o($data,'$data');
	return $ret;
}
?>