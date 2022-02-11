<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

/**
* Search from address, tambom
*
* @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
* @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
* @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
* @return json[{value:org_id, label:org_name},...]
*/
function api_commune($self,$q=NULL,$n = NULL,$p = NULL) {
	sendheader('text/html');
	$q = trim(SG\getFirst($q,$_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],50));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) {
		return '[]';
	}

	$stmt='SELECT DISTINCT `commune`
				FROM %db_person% p
				WHERE `commune` LIKE :q
				ORDER BY CONVERT(`commune` USING tis620) ASC
				LIMIT '.($p-1).','.$n;
				// OR `distname` LIKE :q OR `provname` LIKE :q
	$dbs=mydb::select($stmt,':q','%'.$q.'%');

	$result=array();
	//		$result[]=array('value'=>'q','label'=>'tambon : '.$q.' : '.$tambon);

	foreach ($dbs->items as $rs) {
		$result[] = array('value'=>$rs->commune, 'label'=>htmlspecialchars($rs->commune));
	}
	if (debug('api')) {
		$result[]=array('value'=>'length','label'=>'Charactor length = '.strlen($tambon));
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}

?>