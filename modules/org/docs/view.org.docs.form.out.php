<?php
/**
* Organization Docs Out Form
*
* @param Object $data
* @return String
*/

function view_org_docs_form_out($data=NULL, $options = '{}') {
	$defaults='{debug: false, mode: "edit"}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;
	//$ret .= print_o($options);

	$form = new Form('docs', url('org/docs/o/'.$data->orgid.'/save'), NULL, 'sg-form');

	$form->addConfig('title', 'หนังสือออก');
	$form->addData('checkValid', true);
	if ($options->mode != 'edit') $form->addConfig('readonly', true);

	$form->addField('docid', array('type' => 'hidden', 'value' => $data->docid));
	$form->addField('orgid', array('type' => 'hidden', 'value' => $data->orgid));
	$form->addField('doctype', array('type' => 'hidden', 'value' => 'OUT'));

	$form->addField(
		'docno',
		array(
			'type' => 'text',
			'label' => 'เลขที่หนังสือออก',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->docno),
			'placeholder' => 'eg. 324/2561',
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
		'attnname',
		array(
			'type' => 'text',
			'label' => 'เรียน',
			'class' => '-fill',
			'value' => htmlspecialchars($data->attnname),
		)
	);

	$form->addField(
		'attnorg',
		array(
			'type' => 'text',
			'label' => 'ถึงหน่วยงาน',
			'class' => '-fill',
			'value' => htmlspecialchars($data->attnorg),
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

	if ($options->mode == 'edit') {
		$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>บันทึกหนังสือออก</span>',
				'container' => '{class: "-sg-text-right"}',
				'pretext' => '<a class="btn -link -cancel" href="'.url('org/docs/o/'.$data->orgid.'/out').'"><i class="icon -material -gray">cancel</i><span>ยกเลิก</span></a> ',
			)
		);
	}

	$ret .= $form->build();

	return $ret;
}
?>