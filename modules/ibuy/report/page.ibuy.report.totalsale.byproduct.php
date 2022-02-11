<?php
/**
 * Report total sale quantity
 * 
 * @return String
 */
function ibuy_report_totalsale_byproduct($self,$pid = NULL) {
	if (!user_access('administer ibuys')) return message('error','Access denied');
	$self->theme->title='รายงานยอดขายสินค้า';
	$ret.='<form method="GET" action="'.url('ibuy/report/totalsale/byproduct').'">ค้นหา : <input type="text" name="iq" value="'.$_GET['iq'].'" placeholder="ชื่อสินค้า" /><button class="btn"><i class="icon -material">search</i><span>ค้น</span></button></form>';
	if ($pid) {
		$stmt='SELECT otr.* , o.status,t.title , f.custname
					FROM %ibuy_order% o
						LEFT JOIN %ibuy_ordertr% otr ON otr.oid=o.oid
						LEFT JOIN %ibuy_customer% f ON f.uid=o.uid
						LEFT JOIN %topic% t ON t.tpid=otr.tpid
					WHERE o.status>=0 && otr.tpid=:pid ORDER BY otr.oid ASC';
		$ordertrs=mydb::select($stmt,':pid',$pid);
		user_menu('back','Back to report',url('ibuy/report/totalsale'));
//			$ret.='<p>รายการสั่งซื้อสินค้าของ <strong>'.$rs->name.'</strong> หมายเลขใบสั่งซื้อ <strong>'.$rs->oid.'</strong> เมื่อวันที่ <strong>'.date('d-m-Y H:i',$rs->orderdate).'</strong> อยู่ในสถานะ <strong>'.ibuy_define::status_text($rs->status).'</strong></p>';


		$tables = new Table();
		$tables->header=array('center'=>'ใบสั่งซื้อ','ชื่อสินค้า','ร้านค้า','amt'=>'จำนวนขาย','money'=>'ราคาขาย','money total'=>'รวมเงิน');
		foreach ($ordertrs->items as $rs) {
			$tables->rows[]=array('<a href="'.url('ibuy/report/order/'.$rs->oid).'">'.$rs->oid.'</a>',
												$rs->title,
												$rs->custname,
												number_format($rs->amt,0),
												number_format($rs->price,2),
												number_format($rs->amt*$rs->price,2),
												);
			$amts+=$rs->amt;
			$totals+=$rs->amt*$rs->price;
		}
		$tables->rows[]=array('','','รวมทั้งสิ้น',number_format($amts,0),'',number_format($totals,2));

		$ret .= $tables->build();
	} else {
		$stmt='SELECT tr.tpid,sum(tr.amt) amts,tr.price,t.title
						FROM %ibuy_ordertr% tr
							LEFT JOIN %topic% t ON t.tpid=tr.tpid
							LEFT JOIN %ibuy_order% o USING(`oid`)
						';
		$where[]='o.status>=0';
		if ($_GET['iq']) {
			$where[]='t.title LIKE "%'.addslashes($_GET['iq']).'%"';
		}
		if ($where) $stmt.=' WHERE '.implode(' AND ',$where);
		$stmt.=' GROUP BY tr.tpid ORDER BY amts DESC';
		$orders=mydb::select($stmt);
		//$ret.=print_o($orders);
		$amts=$totals=0;


		$tables = new Table();
		$tables->header=array('no'=>'ลำดับ','ชื่อสินค้า','amt'=>'จำนวนขาย(ชิ้น)','money'=>'ยอดขาย(บาท)','&nbsp;');
		foreach ($orders->items as $rs) {
			$tables->rows[]=array(++$no,
												$rs->title,
												number_format($rs->amts,0),
												number_format($rs->amts*$rs->price,2),
												'<a href="'.url('ibuy/report/totalsale/byproduct/'.$rs->tpid).'"><i class="icon -material">pageview</i></a>',
											);
			$amts+=$rs->amts;
			$totals+=$rs->amts*$rs->price;
		}
		$tables->rows[]=array('','รวมทั้งสิ้น',number_format($amts,0),number_format($totals,2));
		
		$ret .= $tables->build();
	}
	return $ret;
}
?>