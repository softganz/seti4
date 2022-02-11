<?php
function view_project_plan_form_add($tpid,$planid = NULL) {
	$ret.='<h3 class="title -box">เพิ่มแผนงาน/กิจกรรม</h3>';
	$form=new Form(NULL,url('project/plan/'.$tpid.'/add/'.$planid),'aaa','sg-form');
	$form->addData('checkValid',true);
	$form->addData('rel','replace:#project-plan-item-'.$planid);
	$form->addData('complete','closebox');
	$form->addField(
						'title',
						array(
							'type'=>'text',
							'label'=>'ชื่อแผนงาน/กิจกรรม',
							'class'=>'-fill',
							'require'=>true,
							)
						);
	$form->addField(
						'fromdate',
						array(
							'type'=>'text',
							'label'=>'ระยะเวลาดำเนินงาน',
							'class'=>'sg-datepicker',
							'posttext'=>' ถึง <input class="sg-datepicker" type="text"  name="todate" />',
							)
						);
	$form->addField(
						'detail',
						array(
							'type'=>'textarea',
							'label'=>'รายละเอียดกิจกรรม',
							'class'=>'-fill',
							'rows'=>4,
							)
						);
	$form->addField(
						'orgsupport',
						array(
							'type'=>'textarea',
							'label'=>'ภาคีร่วมสนับสนุน',
							'class'=>'-fill',
							'rows'=>4,
							)
						);
	$form->addField(
						'save',
						array(
							'type'=>'button',
							'value'=>'<i class="icon -save -white"></i><span>บันทึกแผนงาน/กิจกรรม</span>'
							)
						);
	$ret.=$form->build();

	return $ret;
}
?>