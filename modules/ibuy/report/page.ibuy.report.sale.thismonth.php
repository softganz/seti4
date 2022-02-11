<?php
/**
 * this_month_sale report
 * 
 * @return String
 */

function ibuy_report_sale_thismonth($self) {
	if (!user_access('administer ibuys')) return message('error','Access denied');
	$month=date('Y-m');
	$self->theme->title='รายงานยอดขายสินค้าประจำเดือน '.$month;
	
	$min_total=cfg('ibuy.franchise.min_total');
	
	// Get order for process monthly market share value
	$stmt='SELECT o.*, f.custname , f.custtype
				FROM %ibuy_order% o
					LEFT JOIN %ibuy_customer% f ON f.uid=o.uid
				WHERE o.status>=0 && FROM_UNIXTIME(o.orderdate,"%Y-%m")=:month ORDER BY o.oid ASC';
	$dbs_order=mydb::select($stmt,':month',$month);
	
	// Calculate order subtotal of month for market value
	$order_subtotal=$order_discount=$order_total=0;


	$tableo = new Table();
	$tableo->caption='ใบสั่งซื้อสินค้าที่นำมาคำนวนค่าการตลาด';
	$tableo->header=array('no'=>'หมายเลขใบสั่งซื้อ','date'=>'วันที่สั่งซื้อ &nabla;','ร้านค้า','T','money subtotal'=>'จำนวนเงิน','money discount'=>'ส่วนลด','money total'=>'รวมเงิน','');
	foreach ($dbs_order->items as $rs) {
		$status=ibuy_define::status_text($rs->status);
		$tableo->rows[]=array($rs->oid,
											date('d-m-Y',$rs->orderdate),
											$rs->custname,
											strtoupper(substr($rs->custtype,0,1)),
											number_format($rs->marketvalue,2),
											number_format($rs->discount,2),
											number_format($rs->total,2),
											'config'=>array('class'=>'status-'.$rs->status)
											);
		$order_subtotal+=$rs->marketvalue;
		$order_discount+=$rs->discount;
		$order_total+=$rs->total;
	}
	$tableo->rows[]=array('','','รวมทั้งสิ้น','',number_format($order_subtotal,2),number_format($order_discount,2),number_format($order_total,2));
	
	// Get franchise in condition member before date 15 of process month and monthly order >= cfg('ibuy.franchise.min_total')
	list($y,$m)=explode('-',$month);
	$y=intval($y);
	$m=intval($m);
	$datein=date('Y-m-d',mktime(0, 0, 0, $m+1, 0-cfg('ibuy.franchise.checkdatein'),   $y));

	$stmt='SELECT f.uid,u.username,f.custname , f.custtype , DATE(u.datein) datein , discount_hold ,
					(SELECT SUM(`leveldiscount`) FROM %ibuy_order% WHERE status>=0 && uid=f.uid AND FROM_UNIXTIME(orderdate,"%Y-%m")=:month ) leveldiscount_totals
				FROM %ibuy_customer% f
					LEFT JOIN %users% u ON u.uid=f.uid
				WHERE f.custtype="Franchise" && DATE_FORMAT(u.datein,"%Y-%m-%d")<=:datein
				HAVING leveldiscount_totals>0
				ORDER BY leveldiscount_totals DESC';
	$dbs_franchise=mydb::select($stmt,':month',$month,':datein',$datein,':min_total',cfg('ibuy.franchise.min_total'));
	// Get level discount information
	$level_discount=cfg('ibuy.franchise.discount');

	// Init franchise calculate variable
	$franchise_discount_info=array();
	$market_franchise_yes=array();
	$level_discount_item_total=0;


	$table_discount = new Table();
	$table_discount->caption='รายละเอียดการคำนวนค่าการตลาด-ส่วนลด';
	$table_discount->id='ibuy-franchise-discount';
	$table_discount->thead=array('no'=>'ลำดับ','ชื่อร้าน &nabla;','T','date'=>'วันที่เป็นสมาชิก','money'=>'ยอดสั่งซื้อประจำเดือน','percent'=>'%','money level-discount'=>'ส่วนลด','money market-value'=>'ค่าการตลาด','ระงับ','money total-discount'=>'รวมส่วนลด');
	foreach ($dbs_franchise->items as $rs) {
		$table_discount->rows[$rs->uid]=array(++$no,
												'<a href="'.url('ibuy/franchise/'.$rs->username).'" target="_blank">'.$rs->custname.'</a>',
												strtoupper(substr($rs->custtype,0,1)),
												sg_date($rs->datein,'d-m-ปปปป'),
												number_format($rs->leveldiscount_totals,2),
												'0.00',
												'0.00',
												'0.00',
												$rs->discount_hold>=0?'ใช่':'-',
												'0.00',
												);
		$franchise_discount_info[$rs->uid]->total=$rs->leveldiscount_totals;
		$franchise_discount_info[$rs->uid]->market=0;
		$franchise_discount_info[$rs->uid]->hold=$rs->discount_hold>=0;
		
		// Evaluate level discount function
		foreach ($level_discount as $level_discount_item) {
			eval('$is_discount='.$rs->leveldiscount_totals.$level_discount_item['cond'].';'); // check total with level condition
			if ($is_discount) {
				// Get level discount value
				$discount_amt=round($rs->leveldiscount_totals*$level_discount_item['discount'],2);
				$table_discount->rows[$rs->uid][5]=($level_discount_item['discount']*100).'%';
				$table_discount->rows[$rs->uid][6]=number_format($discount_amt,2);
				$table_discount->rows[$rs->uid][9]=number_format($discount_amt,2);
				$franchise_discount_info[$rs->uid]->level->percent=$level_discount_item['discount'];
				$franchise_discount_info[$rs->uid]->level->discount=$discount_amt;
				$level_discount_item_total+=$discount_amt;
				break;
			}
		}
		if ($rs->leveldiscount_totals>=$min_total) $market_franchise_yes[]=$rs->uid;
	}
	
	// Calculate market value per franchise
	if ($market_franchise_yes) {
		$market_value_percent=cfg('ibuy.franchise.marketvalue');
		$market_value=round(($order_subtotal * $market_value_percent)/100,2);
		$market_value_per_franchise=round($market_value/count($market_franchise_yes),2);
	
		foreach ($market_franchise_yes as $frid) {
			$total_discount=round($franchise_discount_info[$frid]->level->discount+$market_value_per_franchise);
			$table_discount->rows[$frid][7]=number_format($market_value_per_franchise,2);
			$table_discount->rows[$frid][9]=number_format($total_discount,2);
			$franchise_discount_info[$frid]->market=$market_value_per_franchise;
		}
	}
	
	$table_discount->tfoot='<tr><td colspan="4" align="right">รวมทั้งสิ้น</td><td class="col-money">'.number_format($order_subtotal,2).'</td><td></td><td class="col-money">'.number_format($level_discount_item_total,2).'</td><td class="col-money">'.number_format($market_value,2).'</td><td></td><td class="col-money">'.number_format($level_discount_item_total+$market_value,2).'</td></tr>';

	$ret .= $table_discount->build();

	$ret.='<strong>กระบวนการประมวลผลประจำเดือน</strong>
<ul>
<li>คำนวนยอดสั่งซื้อสินค้ารวมทั้งเดือน</li>
<li>คำนวนค่าการตลาด 3% จากยอดสั่งซื้อสินค้าทั้งเดือน</li>
<li>นำยอดค่าการตลาดมาเป็นส่วนลดให้กับทุกเฟรนไชส์จำนวนเท่า ๆ กัน ตามเงื่อนไขคือ เป็นเฟรนไขน์ก่อนวันที่ 15 ของเดือนและมียอดสั่งสินค้าในเดือนนั้นมากกว่า '.number_format($min_total,2).' บาท</li>
<li>คำนวนส่วนลดสำหรับแต่ละเฟรนไชส์แบบขั้นบันได</li>
</ul>';
	$ret.='<p><strong>เงื่อนไขการคำนวนส่วนลดแบบขั้นบันได</strong></p><ul>';
	foreach (cfg('ibuy.franchise.discount') as $item) {
		$ret.='<li>'.$item['txt'].'</li>';
	}
	$ret.='</ul>';

	$ret .= $tableo->build();

	return $ret;
}
?>