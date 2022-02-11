<?php
/**
 * Report total sale quantity
 * 
 * @return String
 */
function ibuy_admin_report_totalsale($self) {
	$self->theme->title='รายงานยอดขายสินค้า';
	$self->theme->sidebar=R::Page('ibuy.admin.menu');

	if (!user_access('access administrator pages')) return message('error','access denied');

	$month = post('mo');
	$groupBy = post('gr');
	$action = post('act');
	$backUrl = '#close';

	if ($action=='order' && strlen($month)==10) $backUrl=url('ibuy/admin/report/totalsale',array('gr'=>'date','mo'=>sg_date($month,'Y-m')));
	else if ($groupBy=='date' && strlen($month)==10) $backUrl=url('ibuy/admin/report/totalsale',array('gr'=>'date','mo'=>sg_date($month,'Y-m')));

	$ret.='<header class="header -box"><nav class="nav -back"><a class="sg-action" href="'.$backUrl.'" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>รายงานยอดขายสินค้า</h3></header>';

	if ($action == 'order' && $month) {
		$stmt = 'SELECT o.*, f.custname
					FROM %ibuy_order% o
						LEFT JOIN %ibuy_customer% f ON f.uid=o.uid
					WHERE o.status>=0 && FROM_UNIXTIME(o.orderdate,"'.(strlen($month)==7?'%Y-%m':'%Y-%m-%d').'") = :month ORDER BY o.oid ASC';

		$dbs = mydb::select($stmt,':month',$month);

		user_menu('back','Back to report',url('ibuy/report/totalsale/bymonth'));
//			$ret.='<p>รายการสั่งซื้อสินค้าของ <strong>'.$rs->name.'</strong> หมายเลขใบสั่งซื้อ <strong>'.$rs->oid.'</strong> เมื่อวันที่ <strong>'.date('d-m-Y H:i',$rs->orderdate).'</strong> อยู่ในสถานะ <strong>'.ibuy_define::status_text($rs->status).'</strong></p>';


		$tables = new Table();
		$tables->header=array('ใบสั่งซื้อ','date'=>'วันที่','ร้านค้า','money subtotal'=>'จำนวนเงิน','money discount'=>'ส่วนลด','money total'=>'รวมเงิน','');
		$subtotal=$discount=$total=0;
		foreach ($dbs->items as $rs) {
			$status=ibuy_define::status_text($rs->status);
			$tables->rows[]=array($rs->orderno,
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

		//$ret.=print_o($dbs,'$dbs');
		return $ret;
	}

	$where=array();
	$where=sg::add_condition($where,'o.`status`>=0');
	if ($month) $where=sg::add_condition($where,'FROM_UNIXTIME(o.`orderdate`,"%Y-%m")=:month','month',$month);
	$groupByStr='FROM_UNIXTIME(o.orderdate,"%Y-%m")';
	if ($groupBy=='date') $groupByStr='FROM_UNIXTIME(o.orderdate,"%Y-%m-%d")';
	$stmt='SELECT '.$groupByStr.' `label`, sum(`subtotal`) `subtotals` , sum(`discount`) `discounts` , sum(o.total) `totals`, sum(o.franchisorvalue) `franchisorvalues`
					FROM %ibuy_order% o 
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY `label`
					ORDER BY `label` ASC';
	$dbs=mydb::select($stmt,$where['value']);
	$amts=$totals=0;


	$tables = new Table();
	$tables->header=array('date'=>'เดือน - ปี','money subtotal'=>'รวม(บาท)','money discount'=>'ส่วนลด','money total'=>'รวมยอดขาย(บาท)','money franchisorvalue'=>'เจ้าของเฟรนส์ไชน์','money frtotal'=>'ส่วนแบ่ง','&nbsp;');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											'<a class="sg-action" href="'.url('ibuy/admin/report/totalsale',array('gr'=>'date','mo'=>$rs->label, 'act'=>strlen($month)<7 ? NULL : 'order')).'" data-rel="box">'.$rs->label.'</a>',
											number_format($rs->subtotals,2),
											number_format($rs->discounts,2),
											number_format($rs->totals,2),
											number_format($rs->franchisorvalues,2),
											number_format($rs->franchisorvalues*cfg('ibuy.franchise.franchisor')/100,2),
											'<a class="sg-action" href="'.url('ibuy/admin/report/totalsale',array('act'=>'order','mo'=>$rs->label)).'" data-rel="box">ใบสั่งสินค้า</a>',
										);
		$subtotals+=$rs->subtotals;
		$discounts+=$rs->discounts;
		$totals+=$rs->totals;
	}
	$tables->rows[]=array('รวมทั้งสิ้น',number_format($subtotals,2),number_format($discounts,2),number_format($totals,2));

	$ret .= $tables->build();

	//$ret.=mydb()->_query;
	return $ret;
}
?>