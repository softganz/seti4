<?php
/**
 * Proceed shoping cart to checkout step 5
* Created 2019-06-09
* Modify  2019-06-09
*
* @param
* @return String
*/

$debug = true;

function view_ibuy_checkout_step5($cartInfo, $checkoutInfo) {
	$ret = '';

	$form = new Form('checkout',url(q()),'ibuy-checkout','sg-form ibuy-checkout');
	$form->addData('checkValid',true);
	$form->addConfig('title','ข้อมูลการชำระเงิน');

	$form->addText('<p><strong>ชำระเงินด้วยการโอนเงินเข้าบัญชีธนาคาร</strong></p>');
	$form->addText('<p><ul><li>'.implode('</li><li>',cfg('ibuy.payment.account')).'</li></ul></p>');
	$form->addText('<p>หลังจากโอนเงินแล้ว ท่านสามารถกรอกรายละเอียดการโอนได้ที่หน้า "<a href="{url:ibuy/payment}"><b>การชำระเงิน</b></a>" หรือ โทรแจ้งการโอนเงินใด้ที่'.cfg('ibuy.shop.address').'</p>');
	//foreach (cfg('ibuy.payment.account') as $key=>$rs) $form->payacc->options[$rs]=$rs;


	$form->addField(
			'proceedcard',
			array(
				'type' => 'button',
				'items' => array(
					's1' => array(
						'name' => 'st',
						'btnvalue' => 4,
						'value' => '<i class="icon -material">navigate_before</i><span>ย้อนกลับ</span>',
					),
					's3' => array(
						'name' => 'st',
						'btnvalue' => 6,
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