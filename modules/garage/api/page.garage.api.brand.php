<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function garage_api_brand($q = NULL, $n = NULL, $p = NULL) {
	sendheader('text/html');
	$q = SG\getFirst(trim($q),trim(post('q')));
	$n = intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p = intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) return '[]';

	$shopInfo = R::Model('garage.get.shop');
	$shopid = $shopInfo->shopid;
	$shopbranch = array_keys(R::Model('garage.shop.branch',$shopid));

	$stmt = 'SELECT DISTINCT b.`brandid`, b.`brandname`
		FROM %garage_brand% b
		WHERE (b.`shopid` = 0 OR  b.`shopid` IN (:shopbranch)) AND (b.`brandid` LIKE :q OR b.`brandname` LIKE :q)
		ORDER BY CONVERT(b.`brandid` USING tis620) ASC
		LIMIT '.($p-1).','.$n;

	$dbs=mydb::select($stmt,':shopbranch','SET:'.implode(',',$shopbranch), ':q','%'.$q.'%');

	$result=array();
	foreach ($dbs->items as $rs) {
		$desc='';
		$result[] = array(
			'value'=>htmlspecialchars($rs->brandid),
			'label'=>htmlspecialchars($rs->brandname),
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
	return json_encode($result);
}
?>