<?php
/**
* Project Proposal Plan Expense Form
* Created 2019-09-23
* Modify  2019-09-23
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_proposal_info_exp_form($self, $proposalInfo, $actid) {
	$tpid = $proposalInfo->tpid;
	$expid = post('expid');

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>'.($expid?'แก้ไข':'เพิ่ม').'ค่าใช้จ่าย</h3></header>';

	if ($expid) {
		$stmt = 'SELECT `tpid`,`parent`,`gallery` `expcode`,`num1` `amt`, `num2` `unitprice`, `num3` `times`, `num4` `total`,`detail1` `unitname`, `text1` `detail` FROM %project_tr% WHERE `trid` = :expid LIMIT 1';
		$post = mydb::select($stmt, ':expid', $expid);
		//$ret.=print_o($post,'$post');
	}

	$post->unitprice = SG\getFirst($post->unitprice,0);
	$post->times = SG\getFirst($post->times,1);
	$post->amt = SG\getFirst($post->amt,1);
	$post->total = SG\getFirst($post->total,0);

	$expCodeList = model::get_category('project:expcode','catid');
	foreach ($expCodeList as $key => $value) if (empty($value)) unset($expCodeList[$key]);


	$form = new Form('exp',url('project/proposal/'.$tpid.'/info/exp.save/'.$actid),'project-edit-exp','sg-form project-proposal-expform -sg-flex');
	$form->addData('rel','notify');
	//$form->addData('rel','#plan-detail-'.$actid);
	//$form->addData('ret',url('project/develop/plan/single/'.$tpid));
	$form->addData('done','close | load->replace:#project-proposal-plan');
	$form->addData('planid',$actid);
	//$form->addData('callback','projectPlanExpenseAdd');

	$form->addField(
		'expid',
		array(
			'type' => 'hidden',
			'value' => $expid
		)
	);

	$form->addField(
		'expcode',
		array(
			'type' => 'select',
			'label' => 'ประเภทรายจ่าย:',
			'options' => $expCodeList,
			'class' => '-fill',
			'value' => $post->expcode,
			'container' => '{style: "flex: 1 0 90%;"}',
		)
	);

	$form->addField(
		'amt',
		array(
			'type' => 'text',
			'label' => 'จำนวนหน่วย',
			'class' => '-money -fill',
			'placeholder' => 0,
			'autocomplete' => 'OFF',
			'value' => $post->amt
		)
	);

	$form->addField(
		'unitname',
		array(
			'type' => 'select',
			'label' => 'หน่วยนับ:',
			'options' => array('คน'=>'คน','ครั้ง'=>'ครั้ง','เที่ยว'=>'เที่ยว','ชิ้น'=>'ชิ้น','ชุด'=>'ชุด'),
			'class' => '-fill',
			'value' => $post->unitname
		)
	);

	$form->addField(
		'unitprice',
		array(
			'type' => 'text',
			'label' => 'ค่าใช้จ่ายต่อหน่วย(บาท)',
			'class' => '-money -fill',
			'autocomplete' => 'OFF',
			'placeholder' => 0,
			'value' => $post->unitprice
		)
	);

	$form->addField(
		'times',
		array('type'=>'text',
			'label' => 'จำนวนครั้งกิจกรรม',
			'class' => '-numeric -fill',
			'autocomplete' => 'OFF',
			'value' => 1,
			'value' => $post->times
		)
	);

	$form->addField(
		'total',
		array(
			'type' => 'text',
			'label' => 'รวมเงิน(บาท)',
			'class' => '-money -fill',
			'placeholder' => 0,
			'value' => $post->total,
			'readonly' => true
		)
	);

	$form->addField(
		'detail',
		array(
			'type' => 'textarea',
			'label' => 'รายละเอียดค่าใช้จ่าย',
			'class' => '-fill',
			'rows' => 3,
			'value' => $post->detail,
			'container' => '{style: "flex: 1 0 90%;"}',
		)
	);

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'name' => 'save',
			'value' => '<i class="icon -save -white"></i><span>บันทึกค่าใช้จ่าย</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right", style: "flex: 1 0 90%"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<script>
	$("#project-edit-exp input").keyup(function(){
		var total = 0
		var amt = parseFloat($("#edit-exp-amt").val().replace(/,/g, ""))
		var unitprice = parseFloat($("#edit-exp-unitprice").val().replace(/,/g, ""))
		var times = parseFloat($("#edit-exp-times").val().replace(/,/g, ""))
		total = amt * unitprice * times
		$("#edit-exp-total").val(total)
	});
	</script>';
	return $ret;
}
?>