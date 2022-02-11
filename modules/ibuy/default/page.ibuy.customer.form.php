<?php
/**
* iBuy My Dashboard
* Created 2019-11-15
* Modify  2019-11-15
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_customer_form($self, $customerInfo = NULL) {
	if ($customerInfo && !($customerId = $customerInfo->custid)) return $customerId.message('error', 'PROCESS ERROR');


	$headerText = ($customerId ? 'แก้ไข' : 'เพิ่ม') . 'ข้อมูลลูกค้า';

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>'.$headerText.'</h3></header>';

	$form = new Form('customer', url('ibuy/customer/'.$customerId.'/info/save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load->replace:#ibuy-customer-view');

	$form->addField(
		'prename',
		array(
			'label' => 'คำนำหน้านาม',
			'type' => 'text',
			'value' => htmlspecialchars($customerInfo->info->prename),
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
			'value' => htmlspecialchars($customerInfo->info->custname),
			'placeholder' => 'ระบุชื่อลูกค้า',
		)
	);

	$form->addField(
		'custaddress',
		array(
			'label' => 'ที่อยู่',
			'type' => 'text',
			'class' => 'sg-address -fill',
			'value' => htmlspecialchars($customerInfo->info->custaddress),
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
			'value' => htmlspecialchars($customerInfo->info->custzip),
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
			'value' => htmlspecialchars($customerInfo->info->custphone),
		)
	);

	$form->addField('areacode', array('type' => 'hidden', 'value' => $customerInfo->info->areacode));

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

	//$ret .= print_o($customerInfo,'$customerInfo');

	return $ret;
}
?>