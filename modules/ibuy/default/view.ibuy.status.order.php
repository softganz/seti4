<?php
/**
 * Show order status process call from ajax
 *
 * @param Integer $oid
 * @return String and die
 */
function view_ibuy_status_order($oid) {
	if ($_POST['saveremark'] && user_access('administer ibuys')) {
		mydb::query('UPDATE %ibuy_order% SET `remark`=:remark WHERE `oid`=:oid LIMIT 1',':remark',$_POST['remark'],':oid',$oid);
	}
	if (post('act')=='addtr') {
		mydb::query('INSERT INTO %ibuy_ordertr% (`oid`,`tpid`) VALUES (:oid,NULL)',':oid',$oid);
	}
	$stmt='SELECT
					o.*
				, f.`custname` , u.`name` , f.`custaddress` , f.`custzip` 
				, f.`custphone` , f.`custattn`, f.`shippingby`
				FROM %ibuy_order% o
					LEFT JOIN %users% u USING(`uid`)
					LEFT JOIN %ibuy_customer% f USING(`uid`)
				WHERE `oid`=:oid
				ORDER BY `oid` DESC LIMIT 1';
	$order=mydb::select($stmt,':oid',$oid);

	$stmt='SELECT o.*,t.`title`
				FROM %ibuy_ordertr% o
					LEFT JOIN %topic% t USING(`tpid`)
				WHERE o.`oid`=:oid
				ORDER BY t.`title` ASC';
	$ordertr=mydb::select($stmt,':oid',$oid);

	$ret.='<div class="ibuy-order-description"><p>ใบสั่งซื้อหมายเลข : <strong>'.$order->orderno.'</strong><br />ร้าน : <strong>'.$order->custname.'</strong> สั่งโดย : <strong>'.$order->name.'</strong><br />ที่อยู่ : <strong>'.$order->custaddress.'</strong> <strong>'.$order->custzip.'</strong><br />ชื่อผู้ติดต่อ : <strong>'.$order->custattn.'</strong> โทรศัพท์ : <strong>'.$order->custphone.'</strong><br />ส่งสินค้าทาง : <strong>'.SG\getFirst($order->shipto,$order->shippingby).'</strong></p></div>'._NL;

	$isEdit=user_access('administer ibuys');
	$ret.='<div class="sg-inline-edit ibuy-order-remark" data-update-url="'.url('ibuy/admin/update').'">';
	$ret.='<p><strong>หมายเลข EMS/เลขที่ส่งของ : </strong>'.view::inlineedit(array('group'=>'order','fld'=>'emscode','tr'=>$oid),$order->emscode,$isEdit).'<br />วันที่ส่งของ '.view::inlineedit(array('group'=>'order','fld'=>'emsdate','tr'=>$oid,'ret'=>'date:ว ดดด ปปปป', 'datetype'=>'bigint'),$order->emsdate,$isEdit,'datepicker').'</p>';
	$ret.='<p><strong>หมายเหตุ : </strong></p>'.view::inlineedit(array('group'=>'order','fld'=>'remark','tr'=>$oid,'ret'=>'html','button'=>'บันทึก'),$order->remark,$isEdit,'textarea').'';
	$ret.='</div>'._NL;

	$tables = new Table();
	$tables->caption='รายการสินค้า';
	$tables->header=array('no'=>'ลำดับ','รหัส','detail'=>'รายการ','amt'=>'จำนวน','money price'=>'ราคา','money discount'=>'ส่วนลด','money total'=>'รวมเงิน','');
	foreach ($ordertr->items as $trrs) {
		$ui = new Ui();
		if ($isEdit) {
			$ui->add('<a class="sg-action" href="'.url('ibuy/'.$trrs->tpid).'" data-rel="box" data-width="640">รายละเอียดสินค้า</a>');
			$ui->add('<a class="sg-action" href="'.url('ibuy/admin/orderform/'.$oid,array('act'=>'edittr','trid'=>$trrs->otrid)).'" data-rel="box" data-width="640" title="แก้ไข">แก้ไข</a>');
			$ui->add('<a class="sg-action" href="'.url('ibuy/admin/orderform/'.$oid,array('act'=>'cleartr','trid'=>$trrs->otrid)).'" data-confirm="คุณต้องการยกเลิกการสั่งสินค้ารายการนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#main" data-ret="'.url('ibuy/order/'.$oid).'">ยกเลิกรายการ</a>');
			$ui->add('<a class="sg-action" href="'.url('ibuy/admin/orderform/'.$oid,array('act'=>'removetr','trid'=>$trrs->otrid)).'" data-confirm="คุณต้องการลบรายการนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#main" data-ret="'.url('ibuy/order/'.$oid).'">ลบรายการ</a>');
		}
		$menu = '<nav>'.($ui->count() ? sg_dropbox($ui->build()) : '').'</nav>';

		$tables->rows[]=array(
			++$no,
			$trrs->tpid,
			'<a href="'.url('ibuy/'.$trrs->tpid).'">'.SG\getFirst($trrs->description,$trrs->title).'</a>',
			$trrs->amt,
			number_format($trrs->price,2),
			number_format($trrs->discount,2),
			number_format($trrs->total,2),
			$menu,
		);
		$total+=$trrs->amt*$trrs->price;
	}
	if ($isEdit) {
		$tables->rows[]=array('<td></td>','','<a class="sg-action" href="'.url('ibuy/admin/orderform/'.$oid,array('act'=>'addtr')).'" data-rel="box">+เพิ่มสินค้า</a>');
	}
	$tables->rows[]=array('<td></td>','','รวม','','','',number_format($order->subtotal,2),'');
	$tables->rows[]=array('<td></td>','','ส่วนลด','','','',($order->discount > 0 ? '-' : '').number_format($order->discount,2),'');
	$tables->rows[]=array('<td></td>','','ค่าขนส่ง','','','',number_format($order->shipping,2),'');
	$tables->rows[]=array('<td></td>','','รวมทั้งสิ้น','','','','<big>'.number_format($order->total,2).'</big>','');

	$ret.='<div class="ibuy-status-order">'.$tables->build().'</div>';

	$logs=mydb::select('SELECT l.* , u.name FROM %ibuy_log% l LEFT JOIN %users% u ON u.uid=l.uid WHERE keyword="order" AND kid=:kid ORDER BY lid ASC',':kid',$oid);
	$no=0;

	$tables = new Table();
	$tables->caption='บันทึกสถานะ';
	$tables->header=array('no'=>'ลำดับ','โดย','date'=>'วันที่');
	foreach ($logs->items as $lrs) {
		$tables->rows[]=array(++$no,
											$lrs->name,
											date('Y-m-d H:i',$lrs->created),
											'config'=>array('class'=>'status-'.$lrs->status)
										);
		$tables->rows[]=array('<td></td>',
											'<td colspan="3">'.$lrs->detail.'</td>',
											'config'=>array('class'=>'status-'.$lrs->status)
										);
	}
	$ret.='<div class="ibuy-status-log">'.$tables->build().'</div>';
	return $ret;
}
?>