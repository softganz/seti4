<?php
/**
 * Search from farm name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function map_searchdowhat($self, $q = NULL, $n = NULL, $p = NULL) {
	sendheader('text/html');
	$q=SG\getFirst($q,trim($_REQUEST['q']));
	$n=intval(SG\getFirst($item,$_REQUEST['n'],20));
	$p=intval(SG\getFirst($p,$_REQUEST['p'],1));

	if (strpos($q,',')) {
		$q=trim(substr($q,strrpos($q,',')+1));
	}
	if (empty($q)) return '[]';

	$stmt='SELECT DISTINCT `dowhat`
					FROM %map_networks% m
					WHERE m.dowhat LIKE :q
					LIMIT '.($p-1).','.$n;
	$dbs=mydb::select($stmt,':q','%'.$q.'%');

	$result=array();
	$founds=array();
	foreach ($dbs->items as $rs) {
		foreach (explode(',',$rs->dowhat) as $str) {
			$str=trim($str);
			if (preg_match('/'.$q.'/i',$str) && !in_array($str,$founds)) $founds[]=$str;
		}
	}
	foreach ($founds as $str) $result[] = array('label'=>$str);
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
		$result[]=array('label',implode(',',$founds));
	}
	return json_encode($result);
}
?>