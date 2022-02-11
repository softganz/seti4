<?php
/**
 * Proceed shoping cart to checkout step 3
* Created 2019-06-09
* Modify  2019-06-09
*
* @param
* @return String
*/

$debug = true;

function view_ibuy_checkout_step3($cartInfo, $checkoutInfo) {
	$ret = '';

	$form = new Form('checkout',url(q()),'ibuy-checkout','sg-form ibuy-checkout');
	$form->addData('checkValid',true);
	$form->addConfig('title','ข้อมูลในการจัดส่งสินค้า');

	// เลือกได้

	if (!$checkoutInfo->shiptoaddress) {
		unset($checkoutInfo->shipaddress);
		$form->addText('ส่งสินค้าไปตามที่อยู่ในใบเสร็จรับเงิน');
	} else if ($checkoutInfo->shiptoaddress) {
		if (empty($checkoutInfo->shipaddress)) $checkoutInfo->shipaddress = $checkoutInfo->bill;
		$form->addField(
			'firstname',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][firstname]',
				'label' => 'ชื่อ',
				'class' => '-fill',
				'require' => true,
				'maxlength' => 50,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->firstname),
			)
		);

		$form->addField(
			'lastname',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][lastname]',
				'label' => 'นามสกุล',
				'class' => '-fill',
				'require' => true,
				'maxlength' => 50,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->lastname),
			)
		);

		$form->addField(
			'company',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][company]',
				'label' => 'บริษัท',
				'class' => '-fill',
				'maxlength' => 200,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->company),
			)
		);

		$form->addField(
			'email',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][email]',
				'label' => 'อีเมล์',
				'class' => '-fill',
				'require' => true,
				'maxlength' => 50,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->email),
			)
		);

		$form->addField(
			'address1',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][address1]',
				'label' => 'ที่อยู่',
				'class' => '-fill',
				'require' => true,
				'maxlength' => 200,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->address1),
			)
		);

		$form->addField(
			'address2',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][address2]',
				'class' => '-fill',
				'maxlength' => 200,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->address2),
			)
		);

		$form->addField(
			'city',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][city]',
				'label' => 'เมือง/อำเภอ',
				'class' => '-fill',
				'require' => true,
				'maxlength' => 30,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->city),
			)
		);

		$form->addField(
			'province',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][province]',
				'label' => 'รัฐ/จังหวัด',
				'class' => '-fill',
				'require' => true,
				'maxlength' => 30,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->province),
			)
		);

		$form->addField(
			'zipcode',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][zipcode]',
				'label' => 'รหัสไปรษณีย์',
				'maxlength' => 5,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->zipcode),
			)
		);

		$form->addField(
			'phone',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][phone]',
				'label' => 'โทรศัพท์',
				'class' => '-fill',
				'require' => true,
				'maxlength' => 30,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->phone),
			)
		);

		$form->addField(
			'fax',
			array(
				'type' => 'text',
				'name' => 'checkout[shipaddress][fax]',
				'label' => 'โทรสาร',
				'class' => '-fill',
				'maxlength' => 30,
				'value' => htmlspecialchars($checkoutInfo->shipaddress->fax),
			)
		);

	}


	$form->addField(
			'proceedcard',
			array(
				'type' => 'button',
				'items' => array(
					's1' => array(
						'name' => 'st',
						'btnvalue' => 2,
						'value' => '<i class="icon -material">navigate_before</i><span>ย้อนกลับ</span>',
					),
					's3' => array(
						'name' => 'st',
						'btnvalue' => 4,
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