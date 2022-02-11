<?php
/**
 * Proceed shoping cart to checkout step 6
* Created 2019-06-09
* Modify  2019-06-09
*
* @param
* @return String
*/

$debug = true;

function view_ibuy_checkout_step6($cartInfo, $checkoutInfo) {
	$ret = '';

	$form = new Form('checkout',url(q()),'ibuy-checkout','sg-form ibuy-checkout');
	$form->addData('checkValid',true);
	$form->addConfig('title','ตรวจสอบคำสั่งซื้อ');

	$form->addField(
			'proceedcard',
			array(
				'type' => 'button',
				'items' => array(
					's1' => array(
						'name' => 'st',
						'btnvalue' => 5,
						'value' => '<i class="icon -material">navigate_before</i><span>ย้อนกลับ</span>',
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

	$form->addText(R::Page('ibuy.cart.view', $cartInfo));


	$ret .= $form->build();
	$ret .= cfg('ibuy.message.proceed');


	return $ret;
}
?>