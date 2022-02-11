<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function garage_api_repaircode($q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');
	$q=SG\getFirst(trim($q),trim(post('q')));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	$type=post('type');
	if (empty($q)) return '[]';

	$shopInfo = R::Model('garage.get.shop');
	$shopid = $shopInfo->shopid;
	$shopbranch = array_keys(R::Model('garage.shop.branch',$shopid));

	$codeTypeList = array(1 => 'สั่งซ่อม', 2 => 'อะไหล่', 3 => 'ค่าแรง');

	mydb::where('(r.`shopid` = 0 OR r.`shopid` IN ( :shopbranch ))',':shopbranch','SET:'.implode(',',$shopbranch));
	mydb::where('(r.`repaircode` LIKE :q OR r.`repairname` LIKE :q)',':q',''.$q.'%');
	if ($type) mydb::where('r.`repairtype` = :type',':type',$type);

	$stmt = 'SELECT
		r.`repairid`, r.`repaircode`, r.`repairtype`, r.`repairname`
		, r.`priceA`, r.`priceB`, r.`priceC`, r.`priceD`
		FROM %garage_repaircode% r
		%WHERE% 
		ORDER BY CONVERT(r.`repairname` USING tis620) ASC
		LIMIT '.($p-1).','.$n;

	$dbs = mydb::select($stmt);

	$result = array();
	foreach ($dbs->items as $rs) {
		$desc='';
		if (in_array($rs->repairtype, array(1,2))) {
			$desc .= ' A='.number_format($rs->priceA,2);
			$desc .= ' B='.number_format($rs->priceB,2);
			$desc .= ' C='.number_format($rs->priceC,2);
			$desc .= ' D='.number_format($rs->priceD,2);
		} else if ($rs->repairtype == 3) {
			$desc .= ' ('.number_format($rs->priceA,2).') บาท';
		}
		$result[] = array(
			'value'=>$rs->repairid,
			'label'=>htmlspecialchars('['.$rs->repaircode.'] '.$rs->repairname).' ('.$codeTypeList[$rs->repairtype].')',
			'code'=>htmlspecialchars($rs->repaircode),
			'name'=>htmlspecialchars($rs->repairname),
			'desc'=>htmlspecialchars($desc),
			'priceA'=>$rs->priceA,
			'priceB'=>$rs->priceB,
			'priceC'=>$rs->priceC,
			'priceD'=>$rs->priceD,
			'priceR'=>0,
			'priceB1'=>0,
			'priceB2'=>0,
			'priceB3'=>0,
		);
	}
	if ($dbs->_num_rows>=$n) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');
	if (debug('api')) {
		$result[]=array('value'=>'sector','label'=>$sector);
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	if (debug('html')) return print_o($result,'$result');
	return json_encode($result);
}
?>