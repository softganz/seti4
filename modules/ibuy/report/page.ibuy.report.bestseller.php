<?php
/**
 * Best seller report
 * 
 */
function ibuy_report_bestseller($self) {
	$self->theme->title='รายงานสินค้าขายดี';
	$stmt='SELECT otr.`tpid`, t.title, SUM(amt) totals
				FROM %ibuy_ordertr% otr
					LEFT JOIN %topic% t ON t.tpid=otr.tpid
				GROUP BY otr.`tpid`
				ORDER BY totals DESC
				LIMIT 50';

	$dbs=mydb::select($stmt);

	$show_amt=user_access('administer ibuys') || array_intersect(i()->roles,array('admin','franchise','resaler'));

	$tables = new Table();
	$tables->thead['no']='อันดับ';
	$tables->thead[]='ชื่อสินค้า';
	if ($show_amt) $tables->thead['amt']='จำนวนขาย (ชิ้น)';
	foreach ($dbs->items as $rs) {
		unset($row);
		$row[]=++$no;
		$row[]='<a href="'.url('ibuy/'.$rs->tpid).'">'.$rs->title.'</a>';
		if ($show_amt) $row[]=number_format($rs->totals);
		$tables->rows[]=$row;
	}

	$ret .= $tables->build();
	
	return $ret;
}
?>