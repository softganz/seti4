<?php
/**
 * List of monitor items
 *
 * @return String
 */
function ibuy_admin_order($self) {
	$self->theme->title='คำสั่งซื้อ';
	$self->theme->sidebar=R::Page('ibuy.admin.menu');

	$stmt = 'SELECT
						l.`lid`, l.`kid`, l.`created`, l.`amt`, l.`uid`
					, f.`custname`, l.`detail`, o.`orderno`
					FROM %ibuy_log% l
						LEFT JOIN %ibuy_customer% f USING(`uid`)
						LEFT JOIN %ibuy_order% o ON o.`oid`=l.`kid`
					WHERE l.`keyword` = "order" AND l.`status` = 20 AND l.`process` = -1
					ORDER BY lid ASC';

	$dbs = mydb::select($stmt);

	if ($dbs->_num_rows) {
		$tables = new Table();
		$tables->id='payment-list';
		$tables->caption='รายการโอนเงิน';
		$tables->header=array('Order no','date'=>'Log Date','Franchise shop','Transfer Date - Time','money'=>'Amout','Action');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array($rs->orderno,date('d-m-y H:i',$rs->created),
												$rs->custname,
												$rs->detail,
												number_format(abs($rs->amt),2),
												'<a href="'.url('/ibuy/status/monitor/transfer/'.$rs->lid).'">ยืนยันการโอนเงิน</a>'
											);
		}

		$ret .= $tables->build();
	}

	$isAdmin=user_access('administrator ibuy');

	/*
	$ui=new ui();
	if ($isAdmin) $ui->add('<a class="button" href="">รายละเอียด</a>');
	$ret.=$ui->build();
	*/
	$stmt='SELECT
					  o.*
					, f.`custname`
					, f.`custtype`
					, f.`custattn`
					, f.`shippingby`
					, u.`username` , u.`name`
				FROM %ibuy_order% o
					LEFT JOIN %ibuy_customer% f ON f.`uid`=o.`uid`
					LEFT JOIN %users% u ON u.`uid`=o.`uid`
				WHERE o.`status`>=0 && o.`status`!=50
				ORDER BY oid DESC';
	$orders=mydb::select($stmt,':uid',i()->uid);

	$tables = new Table();
	$tables->addClass('ibuy__ordetmonitor');
	$tables->id='order-list';
	$tables->caption='Order in queue';
	$tables->header=array(tr('Order no'),'date'=>tr('Date'),'','Shop','T','money subtotal'=>tr('Subtotal'),'money discount'=>tr('Discount'),'money shipping'=>tr('Shipping'),'money total'=>tr('Total'),'money balance'=>'ค้างชำระ','ขนส่งโดย','Action','status'=>tr('Status'),'');
	foreach ($orders->items as $rs) {
		$status=ibuy_define::status_text($rs->status);
		$tables->rows[]=array($rs->orderno,
											date('d-m-Y',$rs->orderdate).'<br />'.date('H:i',$rs->orderdate),
											'<a class="sg-action" href="'.url('ibuy/admin/member',array('id'=>$rs->uid)).'" data-rel="box"><img class="profile" src="'.model::user_photo($rs->username).'" width="48" height="48" /></a>',
											'<a class="sg-action" href="'.url('ibuy/admin/member',array('id'=>$rs->uid)).'" data-rel="box"><strong style="font-size:1.1em;">'.$rs->custname.'</strong></a><br />('.SG\getFirst($rs->custattn,$rs->name).')',
											$rs->custtype,
											number_format($rs->subtotal,2),
											$rs->discount>0?number_format($rs->discount,2):'',
											$rs->shipping>0?number_format($rs->shipping,2):'',
											number_format($rs->total,2),
											$rs->balance?number_format($rs->balance,2):'-',
											$rs->shippingby,
											$rs->emscode.($rs->emsdate?'<br />('.sg_date($rs->emsdate,'ว ดด ปป').')':''),
											'<a href="'.url('ibuy/order/'.$rs->oid).'" title="ดูรายละเอียดการสั่งสินค้า">'.$status.'</a>',
											$rs->status==40?'<a href="'.url('ibuy/status/closeorder/'.$rs->oid).'" class="sg-action" data-confirm="ปิดสถานะใบสั่งสินค้า กรุณายืนยัน" data-removeparent="tr" data-rel="none" title="ปิดสถานะใบสั่งสินค้า"><i class="icon -unlock"></i></a>':'',
											'config'=>array('class'=>'status-'.$rs->status)
										);
		if ($rs->remark) $tables->rows[]='<tr><td colspan="2"></td><td colspan="12">'.($rs->remark?'หมายเหตุ : '.$rs->remark:'').'</td></tr>';
	}
	$ret .= $tables->build();
	return $ret;
}
?>