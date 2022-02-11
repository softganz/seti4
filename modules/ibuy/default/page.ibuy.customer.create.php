<?php
/**
* iBuy Create New Customer
* Created 2020-01-30
* Modify  2020-01-30
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_customer_create($self) {

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>เพิ่มข้อมูลลูกค้า</h3></header>';

	$form = new Form('customer', url('ibuy/customer/create.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'box:clear');
	//$form->addData('done', 'box');

	$form->addField(
		'prename',
		array(
			'label' => 'คำนำหน้านาม',
			'type' => 'text',
			'value' => htmlspecialchars($data->prename),
			'placeholder' => 'ระบุคำนำหน้านาม',
		)
	);

	$form->addField(
		'custname',
		array(
			'label' => 'ชื่อลูกค้า',
			'type' => 'text',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->custname),
			'placeholder' => 'ระบุชื่อลูกค้า',
		)
	);

	$form->addField(
		'custaddress',
		array(
			'label' => 'ที่อยู่',
			'type' => 'text',
			'class' => 'sg-address -fill',
			'value' => htmlspecialchars($data->custaddress),
			'placeholder' => 'ระบุที่อยู่',
			'attr'=>array('data-altfld'=>'edit-customer-areacode'),
		)
	);

	$form->addField(
		'custzip',
		array(
			'label' => 'รหัสไปรษณีย์',
			'type' => 'text',
			'maxlength' => 5,
			'value' => htmlspecialchars($data->custzip),
			'placeholder' => '00000',
		)
	);

	$form->addField(
		'custphone',
		array(
			'label' => 'โทรศัพท์',
			'type' => 'text',
			'class' => '-fill',
			'maxlength' => 50,
			'value' => htmlspecialchars($data->custphone),
		)
	);

	$form->addField('areacode', array('type' => 'hidden'));

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret .= print_o(post(),'post()');

	return $ret;
}
?>