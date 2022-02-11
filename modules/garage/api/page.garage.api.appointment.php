<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function garage_api_appointment() {
	sendheader('text/html');
	$date=post('date');
	if (empty($date)) return '';
	$date=sg_date($date,'Y-m-d');

	$shopInfo=R::Model('garage.get.shop');
	$shopid=$shopInfo->shopid;
	
	$stmt='SELECT * FROM %garage_job% WHERE `shopid`=:shopid AND `iscarreturned`!="Yes" AND `datetoreturn`=:date';
	$dbs=mydb::select($stmt,':shopid',$shopid, ':date',$date);

	if ($dbs->_empty) return 'ไม่มีรายการนัดรถในวันที่ '.sg_date($date,'d/m/ปปปป');

	$tables = new Table();
	$tables->addClass('-center');
	$tables->caption='รายการนัดรับรถวันที่ '.sg_date($date,'d/m/ปปปป');
	$tables->thead=array('วันที่','เวลา','เลขใบสั่งซ่อม','ทะเบียนรถ');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											sg_date($rs->datetoreturn,'d/m/ปปปป'),
											substr($rs->timetoreturn,0,5),
											$rs->jobno,
											$rs->plate,
											);
	}
	$ret.=$tables->build();

	return $ret;
}
?>