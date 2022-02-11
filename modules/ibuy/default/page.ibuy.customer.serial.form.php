<?php
/**
* iBuy :: Customer Product Serial Form
* Created 2020-01-31
* Modify  2020-01-31
*
* @param Object $self
* @param Object $customerInfo
* @param Int $serialId
* @return String
*/

$debug = true;

function ibuy_customer_serial_form($self, $customerInfo = NULL, $serialId = NULL) {
	if ($customerInfo && !($customerId = $customerInfo->custid)) return $customerId.message('error', 'PROCESS ERROR');

	$serialInfo = NULL;

	if ($serialId) {
		// Get Product Serial Info
		$serialInfo = R::Model('ibuy.serial.get', $serialId, '{debug: false}');
	}

	$headerText = ($serialId ? 'แก้ไข' : 'เพิ่ม') . 'ทะเบียนหมายเลขสินค้า';

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>'.$headerText.'</h3></header>';

	$form = new Form('serial', url('ibuy/customer/'.$customerId.'/info/serial.save'.($serialId ? '/'.$serialId : '')), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');

	if ($serialInfo) {
		$form->addData('done', 'back | load->replace:#ibuy-customer-serial-view | load->replace:#ibuy-customer-view');
	} else {
		$form->addData('done', 'back | load->replace:#ibuy-customer-view');
	}

	$form->addField('tpid', array('type' => 'hidden', 'label' => 'ชื่อสินค้า', 'require' => true, 'value' => $serialInfo->info->tpid));

	$form->addField(
		'productname',
		array(
			'label' => 'ชื่อสินค้า',
			'type' => 'text',
			'class' => 'sg-autocomplete -fill',
			'require' => true,
			'value' => htmlspecialchars($serialInfo->productName),
			'placeholder' => 'ระบุชื่อสินค้า',
			'attr' => array(
				'data-query' => url('ibuy/api/product'),
				'data-altfld' => 'edit-serial-tpid',
			)
		)
	);

	/*
	$form->addField(
		'producttype',
		array(
			'label' => 'ประเภท',
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($serialInfo->info->producttype),
			'placeholder' => 'ระบุประเภท',
		)
	);
	*/

	$form->addField(
		'serial',
		array(
			'label' => 'หมายเลขสินค้า (S/N)',
			'type' => 'text',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 50,
			'value' => htmlspecialchars($serialInfo->info->serial),
			'placeholder' => 'ระบุหมายเลขสินค้า (S/N)',
		)
	);

	$form->addField(
		'machineno',
		array(
			'label' => 'หมายเลขเครื่อง',
			'type' => 'text',
			'class' => '-fill',
			'maxlength' => 25,
			'value' => htmlspecialchars($serialInfo->info->machineno),
			'placeholder' => 'ระบุหมายเลขเครื่อง',
		)
	);

	$form->addField(
		'modelinfo',
		array(
			'label' => 'รุ่น (Model)',
			'type' => 'text',
			'class' => '-fill',
			'maxlength' => 25,
			'value' => htmlspecialchars($serialInfo->info->modelinfo),
			'placeholder' => 'ระบุรุ่น',
		)
	);

	$form->addField(
		'saledate',
		array(
			'label' => 'วันที่ขาย',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'value' => sg_date(SG\getFirst($serialInfo->info->saledate,date('Y-m-d')), 'd/m/Y'),
		)
	);

	$form->addField(
		'registerdate',
		array(
			'label' => 'วันที่ลงทะเบียน',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'value' => sg_date(SG\getFirst($serialInfo->info->date,date('Y-m-d')), 'd/m/Y'),
		)
	);


	$form->addField(
		'warrentydate1',
		array(
			'label' => 'วันที่หมดประกัน',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'value' => sg_date(SG\getFirst($serialInfo->info->warrentydate1,date('Y-m-d')), 'd/m/Y'),
		)
	);

	$form->addField(
		'maintfee',
		array(
			'label' => 'ค่าบำรุงรักษา/ปี (บาท)',
			'type' => 'text',
			'maxlength' => 10,
			'value' => $serialInfo->info->maintfee,
		)
	);



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
	//$ret .= print_o($serialInfo,'$serialInfo');

	return $ret;
}
?>