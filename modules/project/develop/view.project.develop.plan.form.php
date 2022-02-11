<?php
function view_project_develop_plan_form($tpid,$planid=NULL,$options=NULL) {
	$form = new Form(NULL,url('project/develop/plan/'.$tpid.'/add/'.$planid), 'project-develop-plan-form','sg-form project-develop-plan-form -sg-text-left');
	$form->addConfig('title','เพิ่มกิจกรรม');
	$form->addData('checkValid',true);
	if ($planid) $form->addData('rel','replace:#project-plan-item-'.$planid);
	else $form->addData('rel','#project-develop-plan');
	$form->addData('complete','closebox');
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
			'posttext'=>' ถึง <input class="form-text sg-datepicker" type="text"  name="todate" readonly="readonly" />',
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
			'pretext' => '<a class="btn -link -cancel"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</a>',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>