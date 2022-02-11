<?php
/**
* Module Method
* Created 2019-06-01
* Modify  2019-06-01
*
* @param
* @return String
*/

$debug = true;

function view_ibuy_checkout_step1($cartInfo, $checkoutInfo) {
	$ret = '';

	$form = new Form('checkout',url(q()),'ibuy-checkout','sg-form ibuy-checkout');
	$form->addData('checkValid',true);
	$form->addConfig('title','CHECKOUT');

	if ($cartInfo->discount_summary > 0) {
		$alway_use_discount = 0; //cfg('ibuy.alway_use_discount');
		$form->addField(
				'usediscount',
				array(
					'type' => 'checkbox',
					'options' => array('yes' => 'ต้องการใช้ส่วนลดในการสั่งซื้อสินค้าครั้งนี้'),
					'value' => $checkoutInfo->usediscount ? 'yes' : '',
					'readonly' => $alway_use_discount ? 'true' : '',
					'attr' => $alway_use_discount ? 'onclick="return false;"' : '',
					'label' => 'ท่านมียอดส่วนลดสะสมที่สามารถนำมาลดในการสั่งซื้อสินค้าครั้งนี้ จำนวน <strong>'.number_format($cartInfo->discount_summary,2).'</strong> บาท:',
				)
			);
	}

	$form->addField(
			'remark',
			array(
				'type'=>'textarea',
				'label'=>'หมายเหตุ หรือ สั่งสินค้าที่ไม่อยู่ในรายการสินค้า',
				'class'=>'-fill',
				'rows'=>3,
				'value'=>$checkoutInfo->remark,
					'description'=>'ท่านสามารถเขียนหมายเหตุหรือสั่งซื้อสินค้าที่ไม่อยู่ในรายการสินค้าในช่องด้านบน',
				'placeholder' => 'ระบุรายการสินค้าเพิ่มเติม หรือ ข้อความอื่น ๆ',
			)
		);

	$form->addField(
			'shipcode',
			array(
				'label'=>'วิธีการส่งสินค้า:',
				'type'=>'radio',
				'options'=>array('14'=>'EMS ด่วนพิเศษ', '13'=>'ไปรษณีย์ลงทะเบียน', '12'=>'ขนส่งเอกชน: <input name="checkout[shipto]" id="edit-shipto" class="form-text" value="'.htmlspecialchars($checkoutInfo->shipto).'" placeholder="ชื่อขนส่ง" type="text">'),
				'value'=>SG\getFirst($checkoutInfo->shipcode,'14')
			)
		);

	if ($cartInfo->shipping) {
		$form->addField(
				'shipping',
				array(
					'type'=>'checkbox',
					'label'=>'เงื่อนไขในการสั่งซื้อสินค้า:',
					'require'=>true,
					'options'=>array('yes'=>'<strong>ข้าพเจ้าขอยืนยันการสั่งซื้อสินค้า'.(cfg('ibuy.shipping.lower')==0?' และ':'ที่มียอดรวมน้อยกว่า <span style="color:red;">'.number_format(cfg('ibuy.shipping.lower'),2).' บาท</span>').' ข้าพเจ้าจะต้องจ่ายค่าขนส่งต้นทางเป็นเงิน <span style="color:red;">'.number_format(cfg('ibuy.shipping.price'),2).' บาท</span></strong>'),
				)
			);
	}
	if (user_access('buy ibuy product')) {
		$form->addField(
				'proceedcard',
				array(
					'type' => 'button',
					'items' => array(
						's1' => array(
							'name' => 'st',
							'btnvalue' => 2,
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
	}

	//$ret .= R::Page('ibuy.cart.view', $cartInfo);

	return $ret;
}
?>