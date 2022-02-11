<?php
function saveup_treat_summary($self) {
	$para=para(func_get_args());
	$year=$_GET['year']?$_GET['year']:date('Y');

	R::View('saveup.toolbar',$self,'สรุปรายการเบิกค่ารักษาพยาบาลประจำปี '.($year+543),'treat');

	$ret .= '<form method="get" action="'.url('saveup/treat/summary').'"><label>กรุณาเลือก </label><select class="form-select" name="year">';
	foreach (mydb::select('SELECT YEAR(`date`) year,SUM(amount) total FROM %saveup_treat% t GROUP BY YEAR(`date`)')->items as $rs) {
		$ret.='<option value="'.$rs->year.'" '.($rs->year==$year?'SELECTED="SELECTED"':'').'>ปี พ.ศ.'.($rs->year+543).' จำนวน '.number_format($rs->total,2).' บาท</option>';
	}
	$ret.='</select>
	<label>เรียงตาม </label><select class="form-select" name="o">
	<option value="mid">รหัส</option>
	<option value="name">ชื่อ</option>
	<!-- <option value="amount">ยอดรวม</option> -->
	</select>
	<button class="btn -primary" type="submit"><i class="icon -search -white"></i><span>ดู</button></form>';
	switch ($_GET['o']) {
		case 'name' : $order='name';break;
		case 'amount' : $order='amount';break;
		default : $order='tr.mid';break;
	}

	$sql_cmd = 'SELECT DATE_FORMAT(`date`,"%Y-%m") date,tr.mid,CONCAT(fu.firstname," ",fu.lastname) name,sum(tr.amount) amount
						FROM %saveup_treat% AS tr
							LEFT JOIN %saveup_member% fu ON fu.mid=tr.mid
						WHERE YEAR(`date`)=:year
						GROUP BY date,tr.mid
						ORDER BY CONVERT('.$order.' USING tis620) ASC,tr.date ASC';
	$query= mydb::select($sql_cmd,':year',$year,':order',$order);

	if ($query->_empty) return $ret.message('error','ไม่มีรายการเบิกค่ารักษาพยาบาลตามเงื่อนไขที่กำหนด');

	$grids=array();
	foreach ($query->items as $rs) {
		$grids[$rs->mid][$rs->date]=+$rs->amount;
		$names[$rs->mid]=$rs->name;
		$totals[$rs->date]+=$rs->amount;
	}

	$total=0;

	$tables = new Table();
	$tables->addClass('saveup-treat-list');
	$tables->caption=$self->theme->title;
	$tables->thead[]='รหัส';
	$tables->thead[]='สมาชิก';
	for ($i=1;$i<=12;$i++) $tables->thead['money month-'.$i]=sprintf('%02d',$i);
	$tables->thead['money total']='รวม';
	foreach ($grids as $mid=>$row) {
		unset($rows);
		$rows[]=$mid;
		$rows[]=$names[$mid];
		$subtotal=0;
		for ($i=1;$i<=12;$i++) {
			$mykey=$year.'-'.sprintf('%02d',$i);
			$rows[]=($row[$mykey]?number_format($row[$mykey],2):'-');
			$subtotal+=$row[$mykey];
		}
		$rows[]='<strong>'.number_format($subtotal,2).'</strong>';
		$tables->rows[]=$rows;
		$total+=$subtotal;
	}
	$tables->tfoot='<tfoot><tr><td></td><td align="right"><strong>รวมทั้งสิ้น</strong></td>';
	// let's print the international format for the en_US locale
	for ($i=1;$i<=12;$i++) $tables->tfoot.='<td class="item-currency">'.preg_replace('/^0\.00/','-',number_format($totals[$year.'-'.sprintf('%02d',$i)],2)).'</td>';
	$tables->tfoot.='<td class="item-currency"><strong>'.number_format($total,2).'</strong></td></tr></tfoot>';

	$ret .= $tables->build();

	$ret.='<p><strong>รวมทั้งสิ้น '.$query->_num_rows.' ครั้ง '.count($tables->rows).' คน เป็นจำนวนเงิน '.number_format($total,2).' บาท</strong></p>';
	return $ret;
}
?>