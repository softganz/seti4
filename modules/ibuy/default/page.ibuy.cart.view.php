<?php
/**
* Module Method
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_cart_view($self, $cartInfo = NULL) {
	$cartInfo = $cartInfo ? $cartInfo : R::Model('ibuy.cart.get');

	$ret = '';

	$ret .= '<h3>รายการสินค้าในตะกร้าที่จะทำการสั่งซื้อ</h3>';
	$tables = new Table();
	$tables->thead=array('','รหัสสินค้า','รายการ','amt'=>'จำนวน','money'=>'ราคา/หน่วย','money subtotal'=>'รวม','money discount'=>'ส่วนลด*','money total'=>'รวมทั้งหมด');
	foreach ($cartInfo->items as $rs) {
		unset($row);
		$row[]='<a class="sg-action" data-title="ลบสินค้าออกจากตะกร้า" data-confirm="ต้องการลบรายการสินค้าในตะกร้า กรุณายืนยัน?" href="'.url('ibuy/cart/'.$rs->tpid.'/delete').'" data-rel="refresh"><i class="icon -delete"></i></a>';
		$row[]=$rs->tpid;
		$row[]='<a href="'.url('ibuy/'.$rs->tpid).'">'.$rs->title.'</a><br />'.(cfg('ibuy.stock.use')?($rs->balance>=$$rs->amt?'In Stock':'Not in stock; order now and we\'ll deliver when available'):'สินค้าพร้อมส่ง').(cfg('ibuy.resaler.discount')>0?' , '.($rs->isdiscount?'':'ไม่นำมา').'คำนวณส่วนลด':'').(cfg('ibuy.franchise.marketvalue')>0?' , '.($rs->ismarket?'':'ไม่นำมา').'คำนวณค่าการตลาด':'');
		$row[]='<strong>'.($rs->amt>0?number_format($rs->amt):'').'</strong>';
		$row[]=$rs->price>0?number_format($rs->price,2):'';
		$row[]=$rs->subtotal>0?number_format($rs->subtotal,2):'';
		$row[]=$rs->discount>0?'('.number_format($rs->discount,2).')':'';
		$row[]=number_format($rs->total,2);
		$tables->rows[]=$row;
	}
	if ($cartInfo->shipping) {
		$tables->rows[]=array('','','<strong>ค่าขนส่ง</strong>','','','','',number_format($cartInfo->shipping,2));
	}
	$tables->rows[]=array('','','<strong>รวมทั้งสิ้น</strong>','<strong>'.$cartInfo->amt.'</strong>','','<strong>'.number_format($cartInfo->subtotal,2).'</strong>',$cartInfo->discount>0?'('.number_format($cartInfo->discount,2).')':'','<strong>'.number_format($cartInfo->total+$cartInfo->shipping,2).'</strong>');

	$ret .= $tables->build();

	if ($cart->discount_summary) $ret.='<p>หมายเหตุ :</p><ul><li><strong>ส่วนลด*</strong> - ส่วนลดเงินสดที่จะสามารถนำไปใช้ในการสั่งซื้อสินค้าครั้งต่อไป</li></ul>';

	$tables = new Table();
	$tables->caption='รายละเอียดการสั่งซื้อสินค้า';
	$tables->thead=array('รายละเอียด','amt'=>'จำนวน','หน่วย');
	$tables->rows[]=array('จำนวนสินค้าทั้งสิ้น',$cartInfo->amt,'รายการ');
	$tables->rows[]=array('สินค้าที่สามารถลดราคาได้',number_format($cartInfo->discount_yes,2),'บาท');
	$tables->rows[]=array('สินค้าที่ไม่สามารถลดราคาได้',number_format($cartInfo->discount_no,2),'บาท');
	$tables->rows[]=array('รวมราคาสินค้าทั้งสิ้น','<strong><big>'.number_format($cartInfo->subtotal,2).'</big></strong>','บาท');
	if ($cartInfo->discount_summary>0 && cfg('ibuy.alway_use_discount')) {
		$discount=$cartInfo->discount_summary<$cartInfo->discount_yes?$cartInfo->discount_summary:$cartInfo->discount_yes;
	} else {
		$discount=0;
	}
	$tables->rows[]=array('หักส่วนลดสะสม',number_format(-$discount,2),'บาท');
	if ($cartInfo->shipping) $tables->rows[]=array('รวมค่าขนส่ง',number_format($cartInfo->shipping,2),'บาท');
	$tables->rows[]=array('คงเหลือจำนวนเงินที่ต้องชำระ','<strong><big>'.number_format($cartInfo->total-$discount+$cartInfo->shipping,2).'</big></strong>','บาท');

	$ret .= $tables->build();
	return $ret;
}
?>