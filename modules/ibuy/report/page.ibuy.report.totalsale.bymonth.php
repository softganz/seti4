<?php
/**
 * Report total sale quantity
 * 
 * @return String
 */
function ibuy_report_totalsale_bymonth($self,$month = NULL) {
	if (!user_access('administer ibuys')) return message('error','Access denied');
	$self->theme->title='รายงานยอดขายสินค้า - แยกตามเดือน-ปี';
	if ($month) {
		$stmt='SELECT o.*, f.custname
					FROM %ibuy_order% o
						LEFT JOIN %ibuy_customer% f ON f.uid=o.uid
					WHERE o.status>=0 && FROM_UNIXTIME(o.orderdate,"%m-%Y")=:month ORDER BY o.oid ASC';
		$dbs=mydb::select($stmt,':month',$month);
		user_menu('back','Back to report',url('ibuy/report/totalsale/bymonth'));
		//			$ret.='<p>รายการสั่งซื้อสินค้าของ <strong>'.$rs->name.'</strong> หมายเลขใบสั่งซื้อ <strong>'.$rs->oid.'</strong> เมื่อวันที่ <strong>'.date('d-m-Y H:i',$rs->orderdate).'</strong> อยู่ในสถานะ <strong>'.ibuy_define::status_text($rs->status).'</strong></p>';


		$tables = new Table();
		$tables->header=array('no'=>'หมายเลขใบสั่งซื้อ','date'=>'วันที่','ร้านค้า','money subtotal'=>'จำนวนเงิน','money discount'=>'ส่วนลด','money total'=>'รวมเงิน','');
		$subtotal=$discount=$total=0;
		foreach ($dbs->items as $rs) {
			$status=ibuy_define::status_text($rs->status);
			$tables->rows[]=array($rs->oid,
												date('d-m-Y',$rs->orderdate),
												$rs->custname,
												number_format($rs->subtotal,2),
												number_format($rs->discount,2),
												number_format($rs->total,2),
												'<a href="'.url('ibuy/report/order/'.$rs->oid).'">รายละเอียด</a>',
												'config'=>array('class'=>'status-'.$rs->status)
												);
			$subtotal+=$rs->subtotal;
			$discount+=$rs->discount;
			$total+=$rs->total;
		}
		$tables->rows[]=array('','','รวมทั้งสิ้น',number_format($subtotal,2),number_format($discount,2),number_format($total,2));

		$ret .= $tables->build();

	} else {
		$stmt='SELECT FROM_UNIXTIME(o.orderdate,"%m-%Y") month,sum(`subtotal`) `subtotals` , sum(`discount`) `discounts` , sum(o.total) `totals`, sum(o.franchisorvalue) `franchisorvalues`
						FROM %ibuy_order% o 
						WHERE o.status>=0
						GROUP BY FROM_UNIXTIME(o.orderdate,"%Y-%m") ORDER BY o.orderdate ASC';
		$dbs=mydb::select($stmt);
		$amts=$totals=0;


		$tables = new Table();
		$tables->header=array('date'=>'เดือน - ปี','money subtotal'=>'รวม(บาท)','money discount'=>'ส่วนลด','money total'=>'รวมยอดขาย(บาท)','money franchisorvalue'=>'เจ้าของเฟรนส์ไชน์','money frtotal'=>'ส่วนแบ่ง','&nbsp;');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array($rs->month,
												number_format($rs->subtotals,2),
												number_format($rs->discounts,2),
												number_format($rs->totals,2),
												number_format($rs->franchisorvalues,2),
												number_format($rs->franchisorvalues*cfg('ibuy.franchise.franchisor')/100,2),
												'<a href="'.url('ibuy/report/totalsale/bymonth/'.$rs->month).'">รายละเอียด</a>',
											);
			$subtotals+=$rs->subtotals;
			$discounts+=$rs->discounts;
			$totals+=$rs->totals;
		}
		$tables->rows[]=array('รวมทั้งสิ้น',number_format($subtotals,2),number_format($discounts,2),number_format($totals,2));

		$ret .= $tables->build();
	}
	return $ret;
}
?>