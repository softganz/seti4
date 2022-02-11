<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function ibuy_api_franchise($q,$n,$p) {
	sendheader('text/html');
	$q=SG\getFirst(trim($q),trim(post('q')));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],10));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) return '[]';

	if (preg_match('/(^จังหวัด)(.*)/',$q,$out)) {
		//$q=$out[2];
		$field='p.name';
	} else {
		$field='f.custname';
	}

	mydb()->where('f.`custtype`="franchise" AND p.`name` LIKE :name OR f.`custname` LIKE :name',':name','%'.$q.'%');
	$stmt='SELECT f.`uid`, f.`custname`, f.`custaddress`, p.`name` province
					FROM %ibuy_customer% f
						LEFT JOIN %province% p USING(`pid`)
					%WHERE%
					ORDER BY CONVERT(f.`custname` USING tis620) ASC
					LIMIT '.$n;
	$dbs=mydb::select($stmt);
	//echo mydb()->_query;


	$result=array();
	foreach ($dbs->items as $rs) {
		$desc='ร้าน '.htmlspecialchars(strip_tags($rs->custaddress)).' จังหวัด '.$rs->province;
		$result[] = array(
									'value'=>$rs->uid,
									'label'=>htmlspecialchars($rs->custname),
									'desc'=>htmlspecialchars($desc),
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