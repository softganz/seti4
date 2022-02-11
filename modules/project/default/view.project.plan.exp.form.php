<?php
function view_project_plan_exp_form($actid,$expid = NULL) {
	$ret.='<h4>'.($expid?'แก้ไข':'เพิ่ม').'ค่าใช้จ่าย</h4>';
	if ($expid) {
		$post=R::Model('project.expense.plan.get',$expid);
		mydb::select('SELECT `tpid`,`parent`,`gallery` `expcode`,`num1` `amt`, `num2` `unitprice`, `num3` `times`, `num4` `total`,`detail1` `unitname`, `text1` `detail` FROM %project_tr% WHERE `trid`=:expid LIMIT 1',':expid',$expid);
		//$ret.=print_o($post,'$post');
	}

	$post->unitprice=SG\getFirst($post->unitprice,0);
	$post->times=SG\getFirst($post->times,1);
	$post->amt=SG\getFirst($post->amt,1);
	$post->total=SG\getFirst($post->total,0);

	$expCodeList=model::get_category('project:expcode','catid');
	foreach ($expCodeList as $key => $value) if (empty($value)) unset($expCodeList[$key]);


	$form=new Form('exp',url(q()),'project-edit-exp','sg-form plan-expense-form');
	$form->addData('rel','#plan-detail-'.$actid);
	$form->addData('planid',$actid);
	$form->addData('complete','closebox');
	$form->addData('callback','projectPlanExpenseAdd');

	$form->addField(
						'action',
						array(
							'type'=>'hidden',
							'name'=>'action',
							'value'=>'addexp'
							)
						);
	$form->addField(
						'id',
						array(
							'type'=>'hidden',
							'value'=>$actid
							)
						);
	$form->addField(
						'expid',
						array(
							'type'=>'hidden',
							'value'=>$expid
							)
						);
	$form->addField(
						'expcode',
						array(
							'type'=>'select',
							'label'=>'ประเภทรายจ่าย:',
							'options'=>$expCodeList,
							'class'=>'-fill',
							'value'=>$post->expcode
							)
						);
	$form->addField(
						'amt',
						array(
							'type'=>'text',
							'label'=>'จำนวนหน่วย',
							'class'=>'-money -fill',
							'placeholder'=>0,
							'value'=>$post->amt
							)
						);
	$form->addField(
						'unitname',
						array(
							'type'=>'select',
							'label'=>'หน่วยนับ:',
							'options'=>array('คน'=>'คน','ครั้ง'=>'ครั้ง','เที่ยว'=>'เที่ยว','ชิ้น'=>'ชิ้น','ชุด'=>'ชุด'),
							'class'=>'-fill',
							'value'=>$post->unitname
							)
						);
	$form->addField(
						'unitprice',
						array(
							'type'=>'text',
							'label'=>'ค่าใช้จ่ายต่อหน่วย(บาท)',
							'class'=>'-money -fill',
							'placeholder'=>0,
							'value'=>$post->unitprice
							)
						);
	$form->addField(
						'times',
						array('type'=>'text',
							'label'=>'จำนวนครั้งกิจกรรม',
							'class'=>'-numeric -fill',
							'value'=>1,
							'value'=>$post->times
							)
						);
	$form->addField(
						'total',
						array(
							'type'=>'text',
							'label'=>'รวมเงิน(บาท)',
							'class'=>'-money -fill',
							'placeholder'=>0,
							'value'=>$post->total,
							'readonly'=>true
							)
						);
	$form->addField(
							'detail',
							array(
								'type'=>'textarea',
								'label'=>'รายละเอียดค่าใช้จ่าย',
								'class'=>'-fill',
								'rows'=>3,
								'value'=>$post->detail
								)
							);

	$form->addField(
						'submit',
						array(
							'type'=>'button',
							'name'=>'save',
							'value'=>'<i class="icon -save -white"></i><span>บันทึกค่าใช้จ่าย</span>',
							)
						);
	//$form->submit->posttext=' <a href="javascript:void(0)" onclick="$.colorbox.close()">ยกเลิก</a>';

	$ret .= $form->build();
	$ret.='<script>
	$("#project-edit-exp input").keyup(function(){
		var total=0
		var amt=parseFloat($("#edit-exp-amt").val().replace(/,/g, ""))
		var unitprice=parseFloat($("#edit-exp-unitprice").val().replace(/,/g, ""))
		var times=parseFloat($("#edit-exp-times").val().replace(/,/g, ""))
		total=amt*unitprice*times
		$("#edit-exp-total").val(total)
	});
	</script>';
	return $ret;
}
?>