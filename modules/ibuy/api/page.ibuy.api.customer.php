<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function ibuy_api_customer($self) {
	sendheader('text/html');
	$q = trim(post('q'));
	$n = intval(SG\getFirst(post('n'),20));

	if (empty($q)) return '[]';

	mydb::where('c.`custname` LIKE :name OR c.`custcode` LIKE :name OR c.`custphone` LIKE :name OR s.`serial` LIKE :name',':name','%'.$q.'%');

	$stmt = 'SELECT
		c.`custid`, c.`custname`, c.`custaddress`, c.`custphone`
		, CONCAT(X(`location`),",",Y(`location`)) `latlng`
		, X(`location`) `lat`
		, Y(`location`) `lnt`
		, GROUP_CONCAT(s.`serial` SEPARATOR ", ") `serial`
		FROM %ibuy_customer% c
			LEFT JOIN %ibuy_serial% s USING(`custid`)
		%WHERE%
		GROUP BY `custid`
		ORDER BY CONVERT(c.`custname` USING tis620) ASC
		LIMIT '.$n;

	$dbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);


	$result = array();
	foreach ($dbs->items as $rs) {
		$desc = 'ร้าน '.$rs->custaddress.($rs->custphone ? '<br />โทร '.$rs->custphone : '')
			. ($rs->serial ? '<br />S/N : '.$rs->serial : '');
		$result[] = array(
			'value' => $rs->custid,
			'label' => htmlspecialchars($rs->custname),
			'latlng' => $rs->latlng,
			'desc' => $desc,
		);
	}
	if ($dbs->_num_rows>=$n) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');
	if (debug('api')) {
		$result[]=array('value'=>'shopid','label'=>$shopid);
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	if (debug('html')) return print_o($result,'$result');
	return sg_json_encode($result);
}
?>