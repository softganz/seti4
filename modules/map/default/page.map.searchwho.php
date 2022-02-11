<?php
/**
 * Search from farm name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function map_searchwho($self,$mapGroup=1,$q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');
	$mapGroup=intval(SG\getFirst($_REQUEST['gr'],$_REQUEST['mapgroup']));
	$q=trim(SG\getFirst($q,$_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) return '[]';

	$stmt='SELECT *
					FROM %map_networks% m
					WHERE m.who LIKE :q
					ORDER BY m.`who` ASC
					LIMIT '.($p-1).','.$n;

	$stmt='SELECT m.*, CONCAT(X(`latlng`),",",Y(`latlng`)) latlng, X(`latlng`) lat, Y(`latlng`) lnt
				FROM %map_networks% m
				WHERE m.mapgroup=:mapgroup AND m.who LIKE :q
				ORDER BY CONVERT (`who` USING tis620) ASC;';
	$dbs=mydb::select($stmt,':mapgroup',$mapGroup, ':q','%'.$q.'%');

	$result=array();
	foreach ($dbs->items as $rs) {
		$desc=$rs->address;
		$result[] = array('value'=>$rs->mapid, 'label'=>htmlspecialchars($rs->who), 'desc'=>$desc);
	}
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>