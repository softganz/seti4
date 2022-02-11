<?php
/**
 * Process payment
 *
 * @return String
 */
function ibuy_payment($self) {
	$self->theme->title='แจ้งการชำระเงิน';
	$post=(object)post('payment');
	$post->oid=SG\getFirst($post->oid,post('oid'));
	if (empty($_POST)) return $ret.R::View('ibuy.payment.form',$post);

	if (empty($post->oid)) $error[]='โปรดระบุหมาบเลขในสั่งซื้อสินค้า';
	if (empty($post->payacc)) $error[]='โปรดระบุโอนเงินเข้าบัญชี';
	if (empty($post->time)) $error[]='โปรดระบุเวลาในการโอนเงิน';
	if (empty($post->money)) $error[]='โปรดระบุจำนวนเงินที่โอน';
	if ($error) {
		$ret.=message('error',$error);
		$ret.=R::View('ibuy.payment.form',$post);
	} else {
		$post->money = sg_strip_money($post->money);

		$log = 'ชำระ '.$post->payfor.' หมายเลขใบสั่งสินค้า '.$post->orderno.'<br />ผ่าน '.$post->payacc.'<br />เมื่อวันที่ '.$post->date['date'].'-'.$post->date[month].'-'.$post->date['year'].' เวลา '.$post->time.' น.<br >จำนวนเงิน '.$post->money.' บาท<br />หมายเหตุ : '.$post->remark;

		ibuy_model::log('keyword=order','kid='.$post->oid,'status=20','detail='.$log,'amt=-'.$post->money);

		$ret .= message('status','บันทึกรายการจ่ายชำระเงินเรียบร้อย');
		location('ibuy/status');
	}
	return $ret;
}
?>