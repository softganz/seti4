<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_care_plan_tran_add($self, $psnId, $planId, $data = NULL) {
	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{data: "info"}');
	$psnId = $psnInfo->psnId;

	if (!$psnId) return message('error','ไม่มีข้อมูลของผู้ป่วยที่ระบุ');

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) {
		$ret .= message('error',$psnInfo->error);
		return $ret;
	}

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="close"><i class="icon -material">arrow_back</i></a></nav><h3>Add Care Plan Transaction</h3></header>';

	$data->plandate = sg_date(SG\getFirst($data->plandate, date('Y-m-d')),'d/m/Y');
	$form = new Form('data', url('imed/care/'.$psnId.'/plan.tran.save/'.$planId), NULL, 'sg-form');
	$form->addData('checkValid',true);
	$form->addData('rel', 'replace:#imed-care-plan-tran');
	$form->addData('complete','closebox');
	//$form->addData('ret', url('imed/care/'.$psnId.'/plan.tran/'.$planId));

	$form->addField('cptrid', array('type'=>'hidden', 'value'=>$data->cptrid));
	$form->addField(
					'plandate',
					array(
						'type' => 'text',
						'label' => 'วันที่ดูแล',
						'class' => 'sg-datepicker -fill',
						'require' => true,
						//'readonly' => true,
						'value' => sg_date($data->plandate, 'd/m/Y'),
						'placeholder' => '31/12/2560',
					)
				);

	$form->addField(
					'plantime',
					array(
						'type' => 'time',
						'label' => 'เวลา',
						'class' => '-fill',
						'require' => true,
						'readonly' => true,
						'start' => 8,
						'end' => 21,
						'step' => 60,
						'value' => $data->plantime,
						'placeholder' => '31/12/2560',
					)
				);

	$stmt = 'SELECT c.*, p.`name` `parentName`
					FROM %imed_stkcode% c
						LEFT JOIN %imed_stkcode% p ON p.`stkid` = c.`parent`
					WHERE c.`process` = "CAREPLAN" ORDER BY c.`stkid`';
	$formOptions = array();
	foreach (mydb::select($stmt)->items as $value) {
		$formOptions[$value->parentName][$value->stkid] = $value->name;
	}
	//$ret .= print_o($formOptions,'$formOptions');
	$form->addField(
					'carecode',
					array(
						'type' => 'select',
						'label' => 'แผนการดูแล',
						'class' => '-fill',
						'require' => true,
						'options' => array(''=>'== เลือกแผนการดูแล ==')+$formOptions,
						'value' => htmlspecialchars($data->carecode),
					)
				);

	$form->addField(
					'detail',
					array(
						'type' => 'textarea',
						'label' => 'รายละเอียดเพิ่มเติม',
						'class' => '-fill',
						'rows' => 4,
						'value' => $data->detail,
						'placeholder' => 'อธิบายรายละเอียดเพิ่มเติม',
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