<?php
/**
 * Show payment form
 *
 * @param Object $post
 * @return String
 */
function view_ibuy_payment_form($post=null) {
	if (!i()->ok) return '<p class="notify">สมาชิกกรุณาเข้าสู่ระบบ (Sign in) ก่อน เพื่อตรวจสอบยอดบิลค้างชำระ</p>';

	$form=new Form('payment',url('ibuy/payment'),'ibuy-confirm','sg-form');
	$form->addData('checkValid',true);

	$optionsPayment = array('ค่าสินค้า' => 'ค่าสินค้า');
	if (cfg('ibuy.franchise.register.payment')) $optionsPayment['ค่าเฟรนไชส์'] = 'ค่าเฟรนไชส์';
	$optionsPayment['อื่น ๆ'] = 'อื่น ๆ';

	$form->addField(
			'payfor',
			array(
				'type' => 'select',
				'label' => 'ชำระค่า :',
				'class' => '-fill',
				'options' => $optionsPayment,
				'require' => true,
			)
		);

	$orders = mydb::select('SELECT `oid`, `orderno`,`ordertype`,`total`,`balance`,`status` FROM %ibuy_order% WHERE `uid`=:uid AND `balance`>0 ORDER BY `oid` DESC',':uid',i()->uid);
	$optionsOrder = array('' => '- โปรดเลือก -');
	foreach ($orders->items as $rs) $optionsOrder[$rs->oid] = 'เลขที่ : '.$rs->orderno.' - '.ibuy_define::order_type($rs->ordertype).' ยอดค้างชำระ : '.number_format($rs->balance,2).' บาท';

	$form->addField(
			'oid',
			array(
				'type' => 'select',
				'label' => 'เลขที่ใบสั่งซื้อ :',
				'class' => '-fill',
				'require' => true,
				'options' => $optionsOrder,
				'value' => $post->oid,
			)
		);

	$form->payacc->type='radio';
	$form->payacc->label='โอนเงินเข้าบัญชี :';
	$form->payacc->require=true;
	foreach (cfg('ibuy.payment.account') as $key=>$rs) $form->payacc->options[$rs]=$rs;
	$form->payacc->value=$post->payacc;

	$form->date->type='date';
	$form->date->label='วันที่โอน :';
	$form->date->require=true;
	$form->date->year->range='-1,2';
	$form->date->year->type='BC';
	$form->date->value->date=SG\getFirst($post->date['date'],date('d'));
	$form->date->value->month=SG\getFirst($post->date['month'],date('m'));
	$form->date->value->year=SG\getFirst($post->date['year'],date('Y'));

	$form->time->type='text';
	$form->time->label='เวลา (ประมาณ)';
	$form->time->maxlength=5;
	$form->time->require=true;
	$form->time->value=$post->time;
	$form->time->value=htmlspecialchars($post->time);

	if ($post->oid && empty($post->money)) {
		$post->money=mydb::select('SELECT `balance` FROM %ibuy_order% WHERE `oid`=:oid LIMIT 1',':oid',$post->oid)->balance;
	}
	$form->money->type='text';
	$form->money->label='จำนวนเงินที่โอน :';
	$form->money->posttext='(บาท)';
	$form->money->maxlength=10;
	$form->money->require=true;
	$form->money->value = number_format($post->money,2);

	/*
	$form->doc->type='file';
	$form->doc->label='สำเนาใบโอนเงิน (ถ้ามี) :';
	$form->doc->posttext='(ไม่เกิน 150kb)';
	*/
	$form->addField(
			'remark',
			array(
				'type' => 'textarea',
				'label' => 'ข้อความเพิ่มเติม :',
				'rows' => 2,
				'class' => '-fill',
				'value' => $post->remark,
			)
		);

	$form->addField(
			'confirm',
			array(
				'type' => 'button',
				'value' => 'แจ้งการชำระเงิน',
				'container' => '{class: "-sg-text-right"}',
			)
		);
	/*
	$form->button->items->confirm=tr('แจ้งการชำระเงิน');
	$form->button->posttext=tr('or').' <a href="'.url('ibuy/status').'">'.tr('Cancel').'</a>';
	*/
	$ret .= $form->build();

	return $ret;
}
?>