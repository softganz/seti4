<?php
/**
 * Search from proejct develop
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function project_api_develop($self,$q=NULL,$n,$p) {
	sendheader('text/html');
	$q=trim(SG\getFirst($q,$_REQUEST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) return '[]';
	$stmt='SELECT DISTINCT
					p.`tpid`, t.`title`
				FROM %project_dev% p
					LEFT JOIN %topic% t USING(`tpid`)
				WHERE t.`title` LIKE :q
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
		$result[]=array('value'=>'query','label'=>$dbs->_query.print_o(post(),'post()'));
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>