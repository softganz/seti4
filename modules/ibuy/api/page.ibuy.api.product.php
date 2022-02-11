<?php
/**
* iBuy API : Get Product List
* Created 2020-02-03
* Modify  2020-02-03
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_api_product($self) {
	sendheader('text/html');
	$q = trim(post('q'));
	$n = intval(SG\getFirst(post('n'),20));

	if (empty($q)) return '[]';

	mydb::where('t.`title` LIKE :name',':name','%'.$q.'%');

	$stmt = 'SELECT
		t.`tpid`, t.`title`
		FROM %ibuy_product% p
			LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		ORDER BY CONVERT(t.`title` USING tis620) ASC
		LIMIT '.$n;

	$dbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);


	$result = array();
	foreach ($dbs->items as $rs) {
		//$desc = 'ร้าน '.$rs->custaddress.($rs->custphone ? '<br />โทร '.$rs->custphone : '')
		//	. ($rs->serial ? '<br />S/N : '.$rs->serial : '');
		$result[] = array(
			'value' => $rs->tpid,
			'label' => htmlspecialchars($rs->title),
			//'desc' => $desc,
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