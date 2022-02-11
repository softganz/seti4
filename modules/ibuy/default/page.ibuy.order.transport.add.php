<?php
/**
* Module Method
* Created 2019-08-01
* Modify  2019-08-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_order_transport_add($self, $orderInfo) {
	$orderId = $orderInfo->oid;

	$isEdit = user_access('administer ibuys');

	if (!$isEdit) return message('error', 'Access denied');

	$ret = '<header class="header -box"><h3>บันทึกค่าขนส่ง</h3></header>';

	$form = new Form(NULL, url('ibuy/order/'.$orderId.'/transport.save'), NULL, 'sg-form');
	$form->addData('rel', 'none');
	$form->addData('done', 'notify:บันทึกเรียบร้อย | close | load');

	$form->addField(
		'amount',
		array(
			'type' => 'text',
			'label' => 'ค่าขนส่ง (บาท)',
			'class' => '-fill',
			'value' => htmlspecialchars($orderInfo->info->shipping),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>