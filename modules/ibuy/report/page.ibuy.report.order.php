<?php
/**
 * Order report
 *
 * @param Integer $oid
 * @return String
 */
function ibuy_report_order($self, $oid = NULL) {
	if (!user_access('administer ibuys')) return message('error','Access denied');

	$self->theme->title='รายงานการสั่งซื้อสินค้า'.($oid?' - ใบสั่งซื้อหมายเลข '.$oid:'');

	$ret.='<form class="form -inlineitem" method="GET" action="'.url('ibuy/report/order').'"><input class="form-text" type="text" name="iq" value="'.$_GET['iq'].'" size="40" placeholder="หมายเลขใบสั่งสินค้าหรือชื่อร้าน" /><button class="btn" type="submit"><i class="icon -material">search</i><span>ค้น</span></button> ค้นหาหมายเลขใบสั่งสินค้าหรือชื่อร้าน</form>';

	if ($oid) {
		$rs=mydb::select('SELECT o.* , u.username , u.name , f.custname FROM %ibuy_order% o LEFT JOIN %users% u ON u.uid=o.uid LEFT JOIN %ibuy_customer% f ON f.uid=o.uid WHERE oid=:oid ORDER BY oid DESC LIMIT 1',':oid',$oid);
		if (!user_access('administer ibuys') && $rs->uid!=i()->uid) return message('error','Access denied');
		user_menu('back','Back to report',url('ibuy/report/order'));
		$ret.='<p>รายการสั่งซื้อสินค้าของ <a href="'.url('ibuy/franchise/'.$rs->username).'" title="ดูรายละเอียด '.htmlspecialchars($rs->custname).'"><strong>'.$rs->custname.'</strong></a> หมายเลขใบสั่งซื้อ <strong>'.$rs->oid.'</strong> เมื่อวันที่ <strong>'.date('d-m-Y H:i',$rs->orderdate).'</strong> อยู่ในสถานะ <strong>'.ibuy_define::status_text($rs->status).'</strong></p>';
		$ret .= R::View('ibuy.report.order.status',$oid);
	} else {
		$stmt='SELECT o.* , u.`username`, u.`name` , f.`custname`,f.`custtype`
					FROM %ibuy_order% o
						LEFT JOIN %ibuy_customer% f ON f.`uid`=o.`uid`
						LEFT JOIN %users% u ON u.`uid`=f.`uid` ';
		if ($_GET['iq'] && is_numeric($_GET['iq'])) {
			$where[]='o.oid='.addslashes($_GET['iq']);
		} else if ($_GET['iq']) {
			$where[]='f.custname LIKE "'.addslashes($_GET['iq']).'%"';
		}
		if ($where) $stmt.=' WHERE '.implode(' AND ',$where);
		$stmt.=' ORDER BY oid DESC LIMIT 1000';

		$orders = mydb::select($stmt,':uid',i()->uid);


		$tables = new Table();
		$tables->header=array('no'=>'Order no','date'=>'Date','Franchise shop','T','money subtotal'=>'Subtotal','money discount'=>'Discount','money total'=>'Total','money balance'=>'ค้างชำระ','money money-market'=>'ค่าส่วนแบ่งการตลาด','money money-level'=>'ส่วนลดขั้นบันได','Action','status'=>'Status','');
		foreach ($orders->items as $rs) {
			$status=ibuy_define::status_text($rs->status);
			$tables->rows[]=array($rs->oid,
												date('d-m-Y H:i',$rs->orderdate),
												'<a href="'.url('ibuy/franchise/'.$rs->username,array('id'=>$rs->uid)).'" title="ดูรายละเอียด '.htmlspecialchars($rs->custname).'">'.SG\getFirst($rs->custname,'ไม่ระบุ').'</a><br />('.$rs->name.')',
												strtoupper(substr($rs->custtype,0,1)),
												number_format($rs->subtotal,2),
												number_format($rs->discount,2),
												number_format($rs->total,2),
												$rs->balance?number_format($rs->balance,2):'-',
												number_format($rs->marketvalue,2),
												number_format($rs->leveldiscount,2),
												$rs->emscode.($rs->emsdate?'<br />('.sg_date($rs->emsdate,'ว ดด ปป').')':''),
												$status,
												'<a href="'.url('ibuy/report/order/'.$rs->oid).'" title="ดูรายละเอียดใบสั่งสินค้าหมายเลข '.$rs->oid.'">รายละเอียด</a>',
												'config'=>array('class'=>'status-'.$rs->status)
											);
			if ($rs->remark) $tables->rows[]='<tr><td colspan="2"></td><td colspan="7">หมายเหตุ : '.$rs->remark.'</td></tr>';
		}
		$ret .= $tables->build();
	}
	return $ret;
}
?>