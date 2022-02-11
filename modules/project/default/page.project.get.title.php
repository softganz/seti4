<?php
/**
 * Search from organization name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function project_get_title($self,$q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');
	$q=SG\getFirst($q,trim($_GET['q']),trim($_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) return '[]';

	$stmt='SELECT DISTINCT p.`tpid`, p.`prid`, t.`title`, p.`date_from`, p.`date_end`, o.`name`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
		WHERE p.`prtype`="โครงการ" AND (t.`title` LIKE :q OR p.`agrno` LIKE :q OR p.`prid` LIKE :q)
		ORDER BY t.`title` ASC
		LIMIT '.($p-1).','.$n;
	$dbs=mydb::select($stmt,':q','%'.$q.'%');

	$result=array();
	foreach ($dbs->items as $rs) {
		$result[] = array(
			'value'=>$rs->tpid,
			'label'=>htmlspecialchars($rs->title),
			'desc'=>htmlspecialchars($rs->name),
		);
	}
	if ($dbs->_num_rows==$n) $result[]=array('value'=>'...','label'=>'+++ ยังมีอีก +++');
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>