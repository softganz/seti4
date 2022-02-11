<?php
/**
* Project Proposal Plan Form
* Created 2019-09-22
* Modify  2019-09-22
*
* @param Object $self
* @param Object $proposalInfo
* @return String
*/

$debug = true;

function project_proposal_info_plan_form($self, $proposalInfo, $data = NULL) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$ret .= 	'<header class="header -box"><h3>กิจกรรม</h3></header>';

	$form = new Form('plan', url('project/proposal/'.$tpid.'/info/plan.save/'.$planid), 'project-proposal-plan-form', 'sg-form project-proposal-plan-form -sg-text-left');
	$form->addData('checkValid',true);
	if ($data) {
		$form->addData('rel','replace:#project-plan-item-'.$planid);
	} else {
		$form->addData('rel', 'notify');
		//$form->addData('ret', url('project/proposal/'.$tpid.'/info/plan.item/'));
	}
	$form->addData('done','close | load->replace:#project-proposal-plan');
	if ($options->ret) $form->addField('ret',array('type'=>'hidden','value'=>$options->ret));

	$form->addField(
		'title',
		array(
			'type'=>'text',
			'label'=>'ชื่อกิจกรรม',
			'class'=>'-fill',
			'require'=>true,
			'placeholder'=>'ระบุชื่อกิจกรรม',
		)
	);

	$form->addField(
		'fromdate',
		array(
			'type'=>'text',
			'label'=>'ระยะเวลาดำเนินงาน',
			'class'=>'sg-datepicker',
			'readonly'=>true,
			'placeholder' => '31/12/2560',
			'posttext'=>' ถึง <input class="form-text sg-datepicker" type="text"  name="plan[todate]" readonly="readonly" placeholder="31/12/2560" />',
		)
	);

	$form->addField(
		'detail',
		array(
			'type'=>'textarea',
			'label'=>'รายละเอียดกิจกรรม/งบประมาณ/อื่นๆ',
			'class'=>'-fill',
			'rows'=>4,
			'placeholder'=>'ระบุรายละเอียดกิจกรรม รายละเอียดงบประมาณ หรือ รายละเอียดอื่นๆ',
		)
	);

	$form->addField(
		'outputoutcome',
		array(
			'type'=>'textarea',
			'label'=>'ผลผลิต (Output) / ผลลัพธ์ (Outcome)',
			'class'=>'-fill',
			'rows'=>4,
			'placeholder'=>'ระบุรายละเอียดผลผลิต (Output) / ผลลัพธ์ (Outcome)',
		)
	);

	$form->addField(
		'budget',
		array(
			'type'=>'text',
			'label'=>'จำนวนเงินงบประมาณของกิจกรรม (บาท)',
			'class'=>'-fill',
			'placeholder'=>'0.00',
		)
	);

	$form->addField(
		'otherresource',
		array(
			'type'=>'textarea',
			'label'=>'ทรัพยากรอื่น ๆ',
			'class'=>'-fill',
			'rows'=>2,
			'placeholder'=>'ระบุรายละเอียดทรัพยากรอื่น ๆ',
		)
	);

	$form->addField(
		'orgsupport',
		array(
			'type'=>'textarea',
			'label'=>'ภาคีร่วมสนับสนุน',
			'class'=>'-fill',
			'rows'=>2,
			'placeholder'=>'ระบุรายละเอียดภาคีร่วมสนับสนุน',
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'containerclass'=>'-sg-text-right',
			'value'=>'<i class="icon -save -white"></i><span>บันทึกกิจกรรม</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</a>',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>