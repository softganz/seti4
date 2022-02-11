<?php
/**
* Search from address
* Module Method
*
* @param Object $self
* @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
* @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
* @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
* @return json[{value:org_id, label:org_name},...]
*/

$debug = true;

function flood_event_getwhere($self,$q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');
	$q=trim(SG\getFirst($q,$_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) return '[]';

	$stmt='SELECT DISTINCT `where`
				FROM %flood_event%
				WHERE `where` LIKE :q
				ORDER BY CONVERT (`where` USING tis620) ASC;';
	$dbs=mydb::select($stmt, ':q','%'.$q.'%');

	$result=array();
	foreach ($dbs->items as $rs) {
		$result[] = array('value'=>$rs->where, 'label'=>htmlspecialchars($rs->where));
	}
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>