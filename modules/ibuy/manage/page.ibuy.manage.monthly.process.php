<?php
/**
 * Monthly process
 * 
 * @return String
 */
function ibuy_manage_monthly_process($self) {
	$month=$_POST['month'];
	$self->theme->title='ประมวลผลประจำเดือน '.$month;
	
	if (($_POST['select'] || $_POST['process']) && $month) {
		if (cfg('ibuy.process') && cfg('ibuy.process')>=$month) return message('error','เดือน '.$month.' ได้ถูกประมวลผลไปแล้ว ไม่สามารถประมวลผลซ้ำได้');
		
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
					ORDER BY f.custname ASC';
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
		
		$tablep = new Table();
		$tablep->caption='รายละเอียดการประมวลผลประจำเดือน '.$month;
		$tablep->rows[]=array('จำนวนใบสั่งสินค้า',number_format($dbs_order->_num_rows).' รายการ');
		$tablep->rows[]=array('ยอดสั่งซื้อ (ก่อนหักส่วนลด)',number_format($order_subtotal,2).' บาท');
		$tablep->rows[]=array('ค่าการตลาด '.$market_value_percent.'% ( '.$order_subtotal.' x '.$market_value_percent.'/100 )',number_format($market_value,2).' บาท');
		$tablep->rows[]=array('จำนวนเฟรนไชส์ที่ได้รับส่วนแบ่งค่าการตลาด (ยอดสั่งซื้อ >='.$min_total.' บาท)',count($market_franchise_yes).' ราย');
		$tablep->rows[]=array('ส่วนแบ่งค่าการตลาดสำหรับแต่ละเฟรนไชส์ ( '.$market_value.' / '.count($market_franchise_yes).' )',number_format($market_value_per_franchise,2).' บาท');
		$tablep->rows[]=array('จำนวนเฟรนไชส์ที่ไม่ได้รับส่วนแบ่งค่าการตลาด ',$dbs_franchise->_num_rows-count($market_franchise_yes).' ราย');
		$tablep->rows[]=array('ส่วนลดแบบขั้นบันได',number_format($level_discount_item_total,2).' บาท');
		$tablep->rows[]=array('ค่าการตลาด + ส่วนลดแบบขั้นบันได','<strong>'.number_format($market_value+$level_discount_item_total,2).' บาท</strong>');

		$ret .= $tablep->build();

		$table_discount->tfoot='<tr><td colspan="4" align="right">รวมทั้งสิ้น</td><td class="col-money">'.number_format($order_subtotal,2).'</td><td></td><td class="col-money">'.number_format($level_discount_item_total,2).'</td><td class="col-money">'.number_format($market_value,2).'</td><td></td><td class="col-money">'.number_format($level_discount_item_total+$market_value,2).'</td></tr>';

		//$ret.=print_o($franchise_discount_info,'$franchise_discount_info');

		if ($_POST['confirm']=='yes') {
			$stmt='';
			foreach ($franchise_discount_info as $frid=>$discount) {
				$total_discount=$discount->market+$discount->level->discount;
				if ($total_discount>0) {
					if ($discount->hold) {
						$stmt='UPDATE %ibuy_customer% SET `discount`=`discount`+'.($discount->level->discount).' , `discount_hold`=`discount_hold`+'.($discount->market).' WHERE `uid`='.$frid.' LIMIT 1 ;';
					} else {
						$stmt='UPDATE %ibuy_customer% SET `discount`=`discount`+'.$total_discount.' WHERE `uid`='.$frid.' LIMIT 1 ;';
					}
					mydb::query($stmt);
					//						$ret.=mysqli_error();
					//						$ret.='process query = '.mydb()->query;
				}
			}

			$cfg_process->month=$month;
			$cfg_process->subtotal=$order_subtotal;
			$cfg_process->market_value=$market_value;
			$cfg_process->franchise_on_cond=count($market_franchise_yes);
			$cfg_process->market_value_per_franchise=$market_value_per_franchise;
			$cfg_process->franchise_discount_amt=$franchise_discount_info;
			cfg_db('ibuy.process.'.$month,$cfg_process);
			cfg_db('ibuy.process',$month);
			$ret.=message('status','ดำเนินการประมวลผลประจำเดือน <strong>'.$month.'</strong> เรียบร้อย');


			$ret .= $table_discount->build();
			//$ret .= $table_level_discount->build();
			$ret .= $tableo->build();
			
			return $ret;
		}
		
		$form = new Form([
			'action' => url(q()),
			'variable' => 'process',
			'children' => [
				'month' => ['type' => 'hidden', 'name' => 'month', 'value' => $month],
				'password' => ['type' => 'password', 'label' => 'ขอรหัสยืนยัน'],
				'confirm' => [
					'type' => 'radio',
					'label' => 'ขอคำยืนยันในการประมวลผลประจำเดือน',
					'name' => 'confirm',
					'options' => [
						'no' => 'ไม่ ฉันยังไม่พร้อมที่จะทำการประมวลผลประจำเดือน',
						'yes' => 'ใช่ ฉันขอยืนยันให้ทำการประมวลผลประจำเดือน',
					],
				],
				'process' => [
					'type' => 'button',
					'name' => 'process',
					'value' => 'ดำเนินการประมวลผล',
					'posttext' => ' หรือ <a href="'.url('ibuy/manage/monthly_process').'">ยกเลิก</a>',
				],
			],
		]);

		$ret .= $form->build();
		
		$ret .= $table_discount->build();
		//$ret .= $table_level_discount->build();
		$ret .= $tableo->build();

		return $ret;
	}
	
	$ret.='<p><strong>คำเตือน : การประมวลผลประจำเดือนจะกระทำได้เพียงครั้งเดียว และเมื่อทำการประมวลผลแล้วจะไม่สามารถยกเลิกได้</strong></p>';

	$form = new Form([
		'variable' => 'process',
		'action' => url(q()),
		'id' => 'edit-generatecode',
		'children' => [
			'month' => [
				'type' => 'radio',
				'name' => 'month',
				'label' => 'เลือกเดือนเพื่อทำการประมวลผล',
				'options' => (function() {
					$options = [];
					$stmt='SELECT FROM_UNIXTIME(o.orderdate,"%Y-%m") month FROM %ibuy_order% o GROUP BY FROM_UNIXTIME(o.orderdate,"%Y-%m") ORDER BY o.orderdate ASC';
					$dbs=mydb::select($stmt);
					
					$current_month=date('Y-m');
					$last_process=cfg('ibuy.process');
					foreach ($dbs->items as $rs) {
						if (cfg('ibuy.process.'.$rs->month)) continue;
						if ($rs->month<=$last_process) continue;
						if ($rs->month>=$current_month) break;
						$options[$rs->month]=$rs->month;
					}
					return $options;
				})(),
			],
			'next' => [
				'type' => 'button',
				'name' => 'select',
				'value' => 'ถัดไป &raquo;',
				'posttext' => ' หรือ <a href="'.url('ibuy/manage').'">ยกเลิก</a>',
			],
		],
	]);
	$ret .= $form->build();
	
	$ret.='<strong>กระบวนการประมวลผลประจำเดือน</strong>
<ul>
<li>คำนวนยอดสั่งซื้อสินค้ารวมทั้งเดือน</li>
<li>คำนวนค่าการตลาด 3% จากยอดสั่งซื้อสินค้าทั้งเดือน</li>
<li>นำยอดค่าการตลาดมาเป็นส่วนลดให้กับทุกเฟรนไชส์จำนวนเท่า ๆ กัน ตามเงื่อนไขคือ เป็นเฟรนไขน์ก่อนวันที่ 15 ของเดือนและมียอดสั่งสินค้าในเดือนนั้นมากกว่า 5,000. บาท</li>
<li>คำนวนส่วนลดสำหรับแต่ละเฟรนไชส์แบบขั้นบันได</li>
</ul>';
	$ret.='<p><strong>เงื่อนไขการคำนวนส่วนลดแบบขั้นบันได</strong></p><ul>';
	foreach (cfg('ibuy.franchise.discount') as $item) {
		$ret.='<li>'.$item['txt'].'</li>';
	}
	$ret.='</ul>';
	return $ret;
}
?>