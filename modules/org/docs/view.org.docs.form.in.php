<?php
/**
* Organization Docs In Form
*
* @param Object $data
* @return String
*/

function view_org_docs_form_in($data=NULL, $options = '{}') {
	$defaults='{debug: false, mode: "edit"}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	$form = new Form('docs', url('org/docs/o/'.$data->orgid.'/save'), NULL, 'sg-form');
	$form->addData('checkValid', true);

	$form->addConfig('title', 'หนังสือเข้า');
	if ($options->mode != 'edit') $form->addConfig('readonly', true);

	$form->addField('docid', array('type' => 'hidden', 'value' => $data->docid));
	$form->addField('orgid', array('type' => 'hidden', 'value' => $data->orgid));
	$form->addField('doctype', array('type' => 'hidden', 'value' => 'IN'));

	$form->addField(
		'docno',
		array(
			'type' => 'text',
			'label' => 'เลขที่หนังสือ',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->docno),
			'placeholder' => 'eg. 000/2561',
		)
	);


	$form->addField(
		'docdate',
		array(
			'type' => 'text',
			'label' => 'ลงวันที่',
			'class' => 'sg-datepicker -fill',
			'require' => true,
			'value' => htmlspecialchars(sg_date(SG\getFirst($data->docdate,date('Y-m-d')),'d/m/Y')),
		)
	);

	$form->addField(
		'attnorg',
		array(
			'type' => 'text',
			'label' => 'จากหน่วยงาน',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->attnorg),
		)
	);

	$form->addField(
		'attnname',
		array(
			'type' => 'text',
			'label' => 'เรียน',
			'class' => '-fill',
			'value' => htmlspecialchars($data->attnname),
		)
	);

	$form->addField(
		'title',
		array(
			'type' => 'text',
			'label' => 'เรื่อง',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->title),
		)
	);

	$form->addField(
		'detail',
		array(
			'type' => 'textarea',
			'label' => 'รายละเอียดอย่างย่อ',
			'class' => '-fill',
			'value' => $data->detail,
		)
	);

	$form->addField(
		'action',
		array(
			'type' => 'radio',
			'label' => 'เข้าร่วม:',
			'options' => array('GO' => 'ไปเข้าร่วม', 'NOT GO' => 'ไม่ไปเข้าร่วม'),
			'value' => $data->action,
		)
	);

	$form->addField(
		'whoaction',
		array(
			'type' => 'text',
			'label' => 'ใครไปร่วม',
			'class' => '-fill',
			'value' => htmlspecialchars($data->whoaction),
		)
	);

	if ($options->mode == 'edit') {
		$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>บันทึกหนังสือเข้า</span>',
				'container' => '{class: "-sg-text-right"}',
				'pretext' => '<a class="btn -link -cancel" href="'.url('org/docs/o/'.$data->orgid.'/in').'"><i class="icon -material -gray">cancel</i><span>ยกเลิก</span></a> ',
			)
		);
	}

	$ret .= $form->build();

	return $ret;
}
?>