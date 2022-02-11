<?php
function view_saveup_treat_form($post = NULL) {
	$payTypeList = saveup_var::$payType;


	$form = new Form([
		'variable' => 'treat',
		'action' => url(q()),
		'id' => 'edit-treat',
		'class' => 'sg-form edit-treat -sg-flex',
		'checkValid' => true,
	]);

	$form->addText('<div class="personal"><h3>รายละเอียดการเบิกจ่าย</h3>');
	$form->addField('tid',array('type'=>'hidden','value'=>$post->tid));

	$form->addField(
		'ref',
		array(
			'type'=>'text',
			'label'=>'เลขที่เอกสาร',
			'maxlength'=>20,
			'class'=>'-fill',
			'require'=>true,
			'value'=>htmlspecialchars($post->ref)
		)
	);

	$form->addField(
		'date',
		array(
			'type'=>'text',
			'label'=>'วันที่อนุมัติ',
			'class'=>'sg-datepicker -fill',
			'require'=>true,
			'autocomplete'=>'off',
			'value'=>$post->date?sg_date($post->date,'d/m/Y'):'',
		)
	);

	$form->addField(
		'mid',
		array(
			'type'=>'hidden',
			'value'=>htmlspecialchars($post->mid)
		)
	);

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'หมายเลขสมาชิก หรือ ชื่อสมาชิก',
			'maxlength' => 50,
			'class' => 'sg-autocomplete -fill',
			'require' => true,
			'value' => htmlspecialchars($post->name),
			'attr' => array(
				'data-query' => url('saveup/api/member'),
				'data-altfld' => 'edit-treat-mid',
				'data-callback' => 'saveupTreatCurrent',
			),
		)
	);

	$form->addField(
		'amount',
		array(
			'type'=>'text',
			'label'=>'จำนวนเงิน (บาท)',
			'maxlength'=>13,
			'class'=>'-fill',
			'require'=>true,
			'value'=>htmlspecialchars($post->amount)
		)
	);

	$form->addText('<div id="saveup-treat-year"><h3>รายการสรุปค่ารักษาพยาบาล</h3><div id="saveup-treat-summary">'.($post->mid ? 	$ret .= R::Page('saveup.treat.year', NULL, $post->mid) : '').'</div></div></div>');

	$form->addText('<div class="otherinfo -sg-clearfix"><h3>รายละเอียดการรักษาพยาบาล</h3>');

	$form->addField(
		'paytype',
		array(
			'type'=>'select',
			'label'=>'ประเภทค่ารักษาพยาบาล:',
			'require'=>true,
			'class'=>'-fill',
			'options'=>array(''=>'===เลือก===') + $payTypeList,
			'value'=>htmlspecialchars($post->paytype)
		)
	);

	/*
	$form->addField(
		'payfor',
		array(
			'type'=>'text',
			'label'=>'เพื่อเป็นค่า',
			'maxlength'=>50,
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->payfor)
		)
	);
	*/

	$form->addField(
		'disease',
		array(
			'type'=>'text',
			'label'=>'รักษาโรค',
			'maxlength'=>50,
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->disease)
		)
	);

	$form->addField(
		'clinic',
		array(
			'type'=>'text',
			'label'=>'สถานพยาบาล',
			'maxlength'=>50,
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->clinic)
		)
	);

	$form->addField(
		'amphure',
		array(
			'type'=>'text',
			'label'=>'อำเภอ',
			'maxlength'=>50,
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->amphure)
		)
	);

	$form->addField(
		'province',
		array(
			'type'=>'text',
			'label'=>'จังหวัด',
			'maxlength'=>50,
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->province)
		)
	);

	$form->addField(
		'bills',
		array(
			'type'=>'select',
			'label'=>'จำนวนใบเสร็จ',
			'options'=>array(0,1,2,3,4,5,6,7,8,9,10),
			'value'=>htmlspecialchars($post->bills)
		)
	);

	$form->addField(
		'billdate',
		array(
			'type'=>'text',
			'label'=>'ใบเสร็จลงวันที่',
			'class'=>'sg-datepicker -fill',
			'autocomplete'=>'off',
			'value'=>$post->billdate?sg_date($post->billdate,'d/m/Y'):'',
		)
	);

	$form->addField(
		'remark',
		array(
			'type'=>'textarea',
			'label'=>'หมายเหตุ',
			'rows'=>2,
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->remark)
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="btn -link -cancel" href="'.url('saveup/treat/view/'.$post->tid).'"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-full -sg-text-right"}'
		)
	);
	$form->addText('</div>');

	$form->addText('<script type="text/javascript">
	function saveupTreatCurrent() {
		var url = "'.url('saveup/treat/year').'"
		var mid = $("#edit-treat-mid").val()
		var para = {}
		$.post(url+"/"+mid, para, function(html) {
			$("#saveup-treat-summary").html(html)
		})
	}
	</script>');
	return $form;
}
?>