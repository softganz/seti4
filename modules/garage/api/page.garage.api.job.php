<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function garage_api_job($q = NULL, $n = NULL, $p = NULL) {
	sendheader('text/html');
	$q = SG\getFirst(trim($q),trim(post('q')));
	$getItem = SG\getFirst($item,post('item'),20);
	$getPage = intval(SG\getFirst($p, post('page'),1));
	$getShop = post('shop');

	$getShow = post('show');

	if (empty($q)) return '[]';

	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	if ($getItem != '*') {
		$limit = 'LIMIT '.($getPage - 1).','.intval($getItem);
	} else {
		$limit = '';
	}
	mydb::value('$LIMIT$', $limit);

	if ($getShop == '*') {
		//mydb::where('(j.`shopid` = :shopId OR j.`shopid` = :shopParent)',':shopId',$shopId, ':shopParent', $shopInfo->shopparent);
	} else {
		//mydb::where('j.`shopid` = :shopId',':shopId',$shopId);
	}
	mydb::where('(j.`jobno` LIKE :q OR j.`plate` LIKE :q OR c.`customername` LIKE :q)', ':q','%'.$q.'%');
	if ($getShow == 'notreturned') mydb::where('j.`iscarreturned` = "No"');

	$stmt = 'SELECT
		  j.`tpid`, j.`jobno`, j.`brandid`
		, j.`plate`
		, s.`shortname` `shopShopName`
		, j.`customerid`, c.`customername`, c.`customerphone`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %garage_customer% c USING(`customerid`)
		%WHERE%
		ORDER BY CONVERT(j.`jobno` USING tis620) ASC
		$LIMIT$
		';

	$dbs = mydb::select($stmt);

	//debugMsg('<pre>'.mydb()->_query.'</pre>');
	//debugMsg($shopInfo, '$shopInfo');

	$result=array();
	foreach ($dbs->items as $rs) {
		$desc = $rs->customername.($rs->customerphone?' ('.$rs->customerphone.')':'').' - '.$rs->shopShopName;
		$result[] = array(
			'value' => $rs->tpid,
			'label' => htmlspecialchars($rs->jobno.' : '.$rs->plate.' : '.$rs->brandid),
			'plate' => htmlspecialchars($rs->plate),
			'jobno' => htmlspecialchars($rs->jobno),
			'brand' => htmlspecialchars($rs->brandid),
			'shopShopName' => htmlspecialchars($rs->shopShopName),
			'desc' => htmlspecialchars($desc),
		);
	}

	if ($getItem != '*' && $dbs->_num_rows >= $getItem) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');

	if (debug('api')) {
		$result[]=array('value'=>'shopid','label'=>$shopId);
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	if (debug('html')) return print_o($result,'$result');
	return SG\json_encode($result);
}
?>