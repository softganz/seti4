<?php
/**
 * Proceed shoping cart to checkout step 2
* Created 2019-06-09
* Modify  2019-06-09
*
* @param
* @return String
*/

$debug = true;

function view_ibuy_checkout_step2($cartInfo, $checkoutInfo) {
	$ret = '';

	$form = new Form('checkout',url(q()),'ibuy-checkout','sg-form ibuy-checkout');
	$form->addData('checkValid',true);
	$form->addConfig('title','ข้อมูลในการออกใบเสร็จ');

	// เลือกได้
	$form->addField(
			'useoldaddr',
			array(
				'type' => 'select',
				'label' => '',
				'options' => array('defalut' => 'Default Address'),
			)
		);

	$form->addField(
		'firstname',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][firstname]',
			'label' => 'ชื่อ',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 50,
			'value' => htmlspecialchars($checkoutInfo->bill->firstname),
		)
	);

	$form->addField(
		'lastname',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][lastname]',
			'label' => 'นามสกุล',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 50,
			'value' => htmlspecialchars($checkoutInfo->bill->lastname),
		)
	);

	$form->addField(
		'company',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][company]',
			'label' => 'บริษัท',
			'class' => '-fill',
			'maxlength' => 200,
			'value' => htmlspecialchars($checkoutInfo->bill->company),
		)
	);

	$form->addField(
		'license',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][license]',
			'label' => 'เลขประจำตัวผู้เสียภาษีอากรของบริษัท',
			'class' => '-fill',
			'maxlength' => 13,
			'value' => htmlspecialchars($checkoutInfo->bill->license),
		)
	);

	$form->addField(
		'email',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][email]',
			'label' => 'อีเมล์',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 50,
			'value' => htmlspecialchars($checkoutInfo->bill->email),
		)
	);

	$form->addField(
		'address1',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][address1]',
			'label' => 'ที่อยู่',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 200,
			'value' => htmlspecialchars($checkoutInfo->bill->address1),
		)
	);

	$form->addField(
		'address2',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][address2]',
			'class' => '-fill',
			'maxlength' => 200,
			'value' => htmlspecialchars($checkoutInfo->bill->address2),
		)
	);

	$form->addField(
		'city',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][city]',
			'label' => 'เมือง/อำเภอ',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 30,
			'value' => htmlspecialchars($checkoutInfo->bill->city),
		)
	);

	$form->addField(
		'province',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][province]',
			'label' => 'รัฐ/จังหวัด',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 30,
			'value' => htmlspecialchars($checkoutInfo->bill->province),
		)
	);

	$form->addField(
		'zipcode',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][zipcode]',
			'label' => 'รหัสไปรษณีย์',
			'maxlength' => 5,
			'value' => htmlspecialchars($checkoutInfo->bill->zipcode),
		)
	);

	$form->addField(
		'phone',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][phone]',
			'label' => 'โทรศัพท์',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 30,
			'value' => htmlspecialchars($checkoutInfo->bill->phone),
		)
	);

	$form->addField(
		'fax',
		array(
			'type' => 'text',
			'name' => 'checkout[bill][fax]',
			'label' => 'โทรสาร',
			'class' => '-fill',
			'maxlength' => 30,
			'value' => htmlspecialchars($checkoutInfo->bill->fax),
		)
	);

	$form->addField(
		'shiptoaddress',
		array(
			'type' => 'radio',
			'label' => 'ข้อมูลในการจัดส่งสินค้า:',
			'options' => array('ส่งไปยังที่อยู่นี้','ส่งไปยังที่อยู่ที่ต่างกัน'),
			'value' => $checkoutInfo->shiptoaddress,
		)
	);

	$form->addField(
			'proceedcard',
			array(
				'type' => 'button',
				'items' => array(
					's1' => array(
						'name' => 'st',
						'btnvalue' => 1,
						'value' => '<i class="icon -material">navigate_before</i><span>ย้อนกลับ</span>',
					),
					's3' => array(
						'name' => 'st',
						'btnvalue' => 3,
						'value' => '<span>ดำเนินการต่อ</span><i class="icon -material">navigate_next</i>',
					),
					'sep' => array('type'=>'text', 'value' => '&nbsp'),
					'proceed' => !$checkoutInfo->bill ? NULL : array(
						'class' => '-primary',
						'value' => '<i class="icon -material">done_all</i><span>ยืนยันการสั่งซื้อ</span>',
					),
				),
				'pretext' => '<a class="btn -link -cancel" href="'.url('ibuy/product').'"><i class="icon -material -gray">keyboard_arrow_left</i><span>เลือกซื้อสินค้าต่อ</span></a>',
				'container' => '{class: "-sg-text-right"}',
			)
		);
	$ret .= $form->build();
	$ret .= cfg('ibuy.message.proceed');

	return $ret;
}
?>