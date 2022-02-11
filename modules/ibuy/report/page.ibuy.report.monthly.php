<?php
/**
 * Monthly buy
 * 
 */
function ibuy_report_monthly($self) {
	$uid=i()->uid;
	if (empty($uid)) return message('error','Access denied');
	$self->theme->title='รายงานประจำเดือน';
	
	$stmt='SELECT * FROM %ibuy_order% WHERE uid=:uid ORDER BY orderdate DESC';

	$dbs=mydb::select($stmt,':uid',$uid);


	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','amt subtotal'=>'ราคาสินค้า','amt discount'=>'ส่วนลด','amt total'=>'รวมทั้งสิ้น');
	$month=date('m',$dbs->items[0]->orderdate);
	foreach ($dbs->items as $rs) {
		if (date('m',$rs->orderdate)!=$month) {
			$tables->rows[]='<tr><td colspan="3" align="right"><big>รวม</big></td><td class="col-amt total"><big><u>'.number_format($mtotal,2).'</u></big></td></tr>';
			$mtotal=0;
			$month=date('m',$rs->orderdate);
		}
		$tables->rows[]=array(sg_date($rs->orderdate,'ว ดดด ปปปป'),
										number_format($rs->subtotal,2),
										$rs->discount?number_format($rs->discount,2):'',
										number_format($rs->total,2),
										);
		$mtotal+=$rs->total;
	}
	$tables->rows[]='<tr><td colspan="3" align="right"><big>รวม</big></td><td class="col-amt total"><big><u>'.number_format($mtotal,2).'</u></big></td></tr>';

	$ret .= $tables->build();
	return $ret;
}
?>