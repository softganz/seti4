<?php
/**
 * Search from username and name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function project_get_username($self,$q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');
	$q=SG\getFirst($q,trim($_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) return '[]';

	$stmt='SELECT username, name
					FROM %users% u
					WHERE u.`username` LIKE :q OR u.`name` LIKE :q  OR u.email LIKE :q
					ORDER BY u.name ASC
					LIMIT '.($p-1).','.$n;
	$dbs=mydb::select($stmt,':q','%'.$q.'%');

	$result=array();
	foreach ($dbs->items as $rs) {
		$result[] = array('value'=>$rs->username, 'label'=>htmlspecialchars($rs->name.' ('.$rs->username.')'));
	}
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>