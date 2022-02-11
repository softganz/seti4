<?php
/**
 * Project Person API
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function project_api_tag($self, $q = NULL, $n = NULL, $p = NULL) {
	sendheader('text/html');
	$q = trim(SG\getFirst($q, post('q')));
	$n = intval(SG\getFirst($n, post('n'), 20));
	$p = intval(SG\getFirst($p, post('p'), 1));

	$tagList = explode(',', $q);

	$q = end($tagList);
	array_pop($tagList);

	//debugMsg('$q = '.$q);
	//debugMsg($tagList,'$tagList');

	if (empty($q)) return '[]';

	mydb::where('b.`keyname` = "project.info" AND b.`fldname` = "tag" AND b.`flddata` LIKE :q',':q','%'.$q.'%');

	$stmt = 'SELECT
						  b.`fldname`, b.`flddata`
					FROM %bigdata% b
					%WHERE%
					GROUP BY b.`flddata`
					ORDER BY CONVERT(b.`flddata` USING tis620) ASC
					LIMIT '.($p-1).','.$n;

	$dbs = mydb::select($stmt);
	//print_o($dbs,'$dbs',1);

	$result = array();
	foreach ($dbs->items as $rs) {
		$value = implode(',', array_merge($tagList,array($rs->flddata)));
		//debugMsg('$value = '.$value);
		$result[] = array(
									'value' => htmlspecialchars($value),
									'label' => htmlspecialchars($value),
								);
	}
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>