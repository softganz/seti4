<?php
/**
 * Show order status process
 *
 * @param Integer $oid
 * @return String
 */
function view_ibuy_report_order_status($oid) {
	$ordertr=mydb::select('SELECT o.*,t.`title` FROM %ibuy_ordertr% o LEFT JOIN %topic% t ON t.`tpid`=o.`tpid` WHERE o.`oid`=:oid',':oid',$oid);


	$tables = new Table();
	$tables->caption='รายการสินค้า';
	$tables->header=array('no'=>'ลำดับ','สินค้า','amt'=>'จำนวน','money price'=>'ราคาต่อหน่วย','money total'=>'รวมเงิน');
	foreach ($ordertr->items as $trrs) {
		$tables->rows[]=array(++$no,
											'<a href="'.url('ibuy/'.$trrs->tpid).'">'.SG\getFirst($trrs->description,$trrs->title).'</a>',
											$trrs->amt,
											number_format($trrs->price,2),
											number_format($trrs->amt*$trrs->price,2)
											);
		$total+=$trrs->amt*$trrs->price;
	}
	$tables->rows[]='<tr><td colspan="3" align="right"><strong>รวมทั้งสิ้น</strong></td><td colspan="2" class="col-money"><big><strong>'.number_format($total,2).'</strong></big></td></tr>';

	$ret.='<div class="ibuy-status-order">'.$tables->build().'</div>';

	$logs=mydb::select('SELECT l.* , u.name FROM %ibuy_log% l LEFT JOIN %users% u ON u.uid=l.uid WHERE keyword="order" AND kid=:kid ORDER BY lid ASC',':kid',$oid);

	$no=0;

	$tables = new Table();
	$tables->caption='บันทึกสถานะ';
	$tables->header=array('no'=>'ลำดับ','date'=>'วันที่','โดย','รายละเอียด');
	foreach ($logs->items as $lrs) {
		$tables->rows[]=array(++$no,
											date('Y-m-d H:i',$lrs->created),
											$lrs->name,
											$lrs->detail,
											'config'=>array('class'=>'status-'.$lrs->status)
										);
	}

	$ret.='<div class="ibuy-status-log">'.$tables->build().'</div>';
	return $ret;
}
?>