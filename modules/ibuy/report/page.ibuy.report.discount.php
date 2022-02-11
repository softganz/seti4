<?php
/**
 * Discount calculate
 *
 * @return String
 */
function ibuy_report_discount($self) {
	if (!user_access('administer ibuys')) return message('error','Access denied');
	
	$dbs=mydb::select('SELECT p.tpid , t.title , p.listprice , p.retailprice , p.resalerprice , p.balance FROM %ibuy_product% p LEFT JOIN %topic% t ON t.tpid=p.tpid WHERE available=0');

	$tables = new Table();
	$tables->caption='รายงานการคำนวณส่วนลด';
	$tables->thead[]='ชื่อร้าน';

	$initrow['name']='';
	foreach (cfg() as $key=>$value) {
		if (substr($key,0,13)!='ibuy.process.') continue;
		$months[$value->month]=$value;
		$initrow['market-'.$value->month]=0;
		$initrow['level-'.$value->month]=0;
		$tables->thead['money market-'.$value->month]=$value->month;
		$tables->thead['money level-'.$value->month]=$value->month;
	}
	$initrow['total']=0;
	$tables->thead['money total']='รวม';

	$dbs = mydb::select('SELECT `uid`,`custname` FROM %ibuy_customer% WHERE `custtype`="franchise" ORDER BY `custname` ASC');

	foreach ($dbs->items as $rs) {
		$users[$rs->uid]=$rs->custname;
		$initrow['name']=$rs->custname;
		$tables->rows[$rs->uid]=$initrow;
	}
	
	foreach ($months as $key=>$value) {
		foreach ($value->franchise_discount_amt as $uid=>$rs) {
			$tables->rows[$uid]['market-'.$value->month]=number_format($rs->market,2);
			$tables->rows[$uid]['level-'.$value->month]=number_format($rs->level->discount,2);
			$tables->rows[$uid]['total']+=$rs->market+$rs->level->discount;
		}
	}
	
	$ret .= $tables->build();

	return $ret;
}
?>