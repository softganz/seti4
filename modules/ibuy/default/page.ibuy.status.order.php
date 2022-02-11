<?php
/**
 * Show order list & status
 *
 * @param Integer $oid to view status process of order id
 * @return String
 */
function ibuy_status_order($self, $oid = NULL) {
	$self->theme->title='Order status process';
	if ($_SESSION['message']) {
		$ret.=$_SESSION['message'];
		unset($_SESSION['message']);
	}
	if ($oid) {
		$stmt='SELECT o.* , u.`name`
						FROM %ibuy_order% o
							LEFT JOIN %users% u USING(`uid`)
						WHERE `oid`=:oid
						ORDER BY `oid` DESC LIMIT 1';
		$rs=mydb::select($stmt,':oid',$oid);
		if (!user_access('administer ibuys') && $rs->uid!=i()->uid) return message('error','Access denied');
		$ret.='<p>รายการสั่งซื้อสินค้าของ <strong>'.$rs->name.'</strong> หมายเลขใบสั่งซื้อ <strong>'.$rs->orderno.'</strong> เมื่อวันที่ <strong>'.sg_date($rs->orderdate,'ว ดด ปปปป H:i').' น.</strong> อยู่ในสถานะ <strong>'.ibuy_define::status_text($rs->status).'</strong></p>';
		$ret.=R::View('ibuy.status.order',$oid);
	} else {
		$stmt='SELECT o.* , u.`name`, l.`status` transferStatus, f.`custname`, f.`custattn`
						FROM %ibuy_order% o
							LEFT JOIN %users% u USING(`uid`)
							LEFT JOIN %ibuy_customer% f USING(`uid`)
							LEFT JOIN %ibuy_log% l ON l.`kid`=o.`oid` AND l.`status`=20
						WHERE o.`uid`=:uid
						GROUP BY o.`oid`
						ORDER BY o.`oid` DESC';

		$orders=mydb::select($stmt,':uid',i()->uid);

		$tables = new Table();
		$tables->addClass('ibuy__orderstatus');
		$tables->caption='ใบสั่งสินค้าของฉัน';
		$tables->header=array('เลขที่ใบสั่งซื้อ','date'=>'วันที่สั่งซื้อ','ร้านค้า/ชื่อผู้ติดต่อ','money subtotal'=>tr('Subtotal'),'money discount'=>tr('Discount'),'money shipping'=>tr('Shipping'),'money total'=>'รวมเงิน','money balance'=>tr('ค้างชำระ'),'Action','status'=>'สถานะ','');
		foreach ($orders->items as $rs) {
			$status=ibuy_define::status_text($rs->status);
			$tables->rows[]=array(
												$rs->orderno,
												sg_date($rs->orderdate,'ว ดด ปปปป H:i'),
												$rs->custname.'<br />('.$rs->name.($rs->name!=$rs->custattn?'/'.$rs->custattn:'').')',
												number_format($rs->subtotal,2),
												number_format($rs->discount,2),
												$rs->shipping>0?number_format($rs->shipping,2):'',
												number_format($rs->total,2),
												$rs->balance?number_format($rs->balance,2):'-',
												$rs->emscode,
												$status,
												'<a href="'.url('ibuy/order/'.$rs->oid).'">รายละเอียด</a>'.($rs->balance>0?' | <a href="'.url('ibuy/payment',array('payment[oid]'=>$rs->oid)).'">แจ้งโอนเงิน</a>'.($rs->transferStatus?' (แจ้งโอนเงินแล้ว)':''):''),
												'config'=>array('class'=>'status-'.$rs->status)
											);
			if ($rs->remark) $tables->rows[]=array('<td colspan="7">'.$rs->remark.'</td>');
		}

		$ret .= $tables->build();
	}
	return $ret;
}
?>