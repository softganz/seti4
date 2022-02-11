<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function garage_api_apmast($q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');
	$id=SG\getFirst(trim($id),trim(post('id')));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],200));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	$cond=post('cond');
	if (empty($id)) return '[]';

	$shopInfo=R::Model('garage.get.shop');
	$shopid=$shopInfo->shopid;

	$condMsg=array('nobill'=>'วางบิล','nopaid'=>'จ่ายเงิน');;

	mydb::where('a.`shopid`=:shopid AND a.`apid`=:apid',':shopid',$shopid, ':apid',$id);
	if ($cond=='nobill') mydb::where('a.`paidid` IS NULL');
	else if ($cond=='nopaid') mydb::where('a.`paidid` IS NULL');
	$stmt='SELECT a.*, ap.`apname`
		FROM %garage_apmast% a
			LEFT JOIN %garage_ap% ap USING(`apid`)
		%WHERE%
		ORDER BY `apid` ASC, `rcvdate` ASC';

		//LIMIT '.($p-1).','.$n;

	$dbs=mydb::select($stmt);
	//debugMsg($dbs,'$dbs');

	if ($dbs->_empty) return '<p class="notify">ไม่มีใบรับของที่ยังไม่'.$condMsg[$cond].'ของผู้จำหน่ายนี้</p>';

	$tables = new Table();
	$tables->addClass('-center');
	$tables->id='garage-insurerqt';
	$tables->thead=array('','ผู้จำหน่าย','เลขที่ใบรับของ','วันที่รับของ','จำนวนเงิน','วันกำหนดจ่ายเงิน');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			'checkbox'=>'<input type="checkbox" name="rcvid[]" value="'.$rs->rcvid.'" />',
			$rs->apname,
			$rs->rcvno,
			sg_date($rs->rcvdate,'d/m/ปปปป'),
			number_format($rs->grandtotal,2),
			$rs->duedate?sg_date($rs->duedate,'d/m/ปปปป'):'',
		);
		$desc='';
		$result[] = array(
			'value'=>htmlspecialchars($rs->insurerid),
			'label'=>htmlspecialchars($rs->insurername),
			'phone'=>htmlspecialchars($rs->insurerphone),
		);
	}
	$ret=$tables->build();
	return $ret;
}
?>