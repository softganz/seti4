<?php
/**
 * Show product not noavailable
 *
 * @return String
 */
function ibuy_report_noavailable($self) {
	if (!user_access('administer ibuys')) return message('error','Access denied');

	$dbs = mydb::select('SELECT p.tpid , t.title , p.listprice , p.retailprice , p.resalerprice , p.balance FROM %ibuy_product% p LEFT JOIN %topic% t ON t.tpid=p.tpid WHERE available=0');


	$tables = new Table();
	$tables->caption='รายชื่อสินค้างดจำหน่าย';
	$tables->header=array('no'=>'ลำดับ','สินค้า','money price-retail'=>'ราคาขายหน้าร้าน<br />(listprice)','money price-resaler'=>'ราคาขายสมาชิก<br />(resalerprice)','money price-franchise'=>'ราคาเฟรนส์ไชน์<br />(retailprice)');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(++$no,
											'<a href="'.url('ibuy/'.$rs->tpid).'">'.SG\getFirst($rs->title,'==สินค้าไม่มีชื่อ==').'</a>',
											$rs->listprice,
											$rs->resalerprice,
											$rs->retailprice
											);
	}

	$ret .= $tables->build();
	
	return $ret;
}
?>