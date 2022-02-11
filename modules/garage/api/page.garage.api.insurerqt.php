<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function garage_api_insurerqt($q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');

	$id = SG\getFirst(trim($id),trim(post('id')));
	$n = intval(SG\getFirst($item,$_GET['n'],$_POST['n'],200));
	$p = intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	$cond = post('cond');
	if (empty($id)) return '[]';

	$shopInfo = R::Model('garage.get.shop');
	$shopid = $shopInfo->shopid;

	$condMsg = array('nobill'=>'วางบิล','norcv'=>'รับเงิน', 'noinvoice' => 'แจ้งหนี้');

	$ret = '<header class="header -hidden">'._HEADER_BACK.'<h3>ใบเสนอราคา</h3></header>';

	mydb::where('(j.`shopid` = :shopid OR s.`shopparent` = :shopid ) AND qt.`insurerid` = :insurerid',':shopid',$shopid, ':insurerid',$id);
	if ($cond == 'nobill') mydb::where('qt.`replyprice` > 0 AND `billid` IS NULL');
	else if ($cond == 'norcv') mydb::where('qt.`replyprice` > 0 AND `rcvid` IS NULL');
	else if ($cond == 'noinvoice') mydb::where('qt.`replyprice` > 0 AND `invoiceid` IS NULL AND `billid` IS NULL AND `rcvid` IS NULL');

	$stmt = 'SELECT qt.*, j.`jobno`, j.`rcvdate`
		, s.`shortname` `shopShortName`
		FROM %garage_qt% qt
			LEFT JOIN %garage_job% j USING(`tpid`)
			LEFT JOIN %garage_shop% s USING(`shopid`)
		%WHERE%
		ORDER BY `tpid` ASC
		LIMIT '.($p-1).','.$n;

	$dbs = mydb::select($stmt);

	if ($dbs->_empty) return $ret.'<p class="notify">ไม่มีใบเสนอราคาที่ยังไม่'.$condMsg[$cond].'ของบริษัทประกันนี้</p>';

	$tables = new Table();
	$tables->addClass('-center');
	$tables->id = 'garage-insurerqt';
	$tables->thead = array('','เลขเคลม','เลขที่ใบเสนอราคา','วันที่เสนอราคา','จำนวนเงิน','เลข Job <i class="icon -sort"></i>','วันที่รับรถ');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'checkbox' => '<input class="-hidden" type="checkbox" name="qtid[]" value="'.$rs->qtid.'" /><i class="icon qtcheck -material -gray">check_circle</i>',
			$rs->insuclaimcode,
			$rs->qtno,
			sg_date($rs->qtdate,'d/m/ปปปป'),
			number_format($rs->replyprice,2),
			$rs->jobno,
			sg_date($rs->rcvdate,'d/m/ปปปป'),
			'config' => array('class' => '-shop-'.$rs->shopShortName)
		);

		$desc = '';

		$result[] = array(
			'value'=>htmlspecialchars($rs->insurerid),
			'label'=>htmlspecialchars($rs->insurername),
			'phone'=>htmlspecialchars($rs->insurerphone),
		);
	}

	$ret .= $tables->build();
	$ret .= '<p class="notify">เลือก <b>ใบเสนอราคา</b> ที่ <b>ยังไม่'.$condMsg[$cond].'</b> ของบริษัทประกันให้เรียบร้อยก่อนที่จะสร้าง'.$condMsg[$cond].'ใบใหม่</p>';
	return $ret;
}
?>