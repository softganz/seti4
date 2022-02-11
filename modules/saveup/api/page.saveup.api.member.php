<?php
/**
 * Search from farm name
 * 
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function saveup_api_member($self, $q = NULL, $n = NULL, $p = NULL) {
	sendheader('text/html');
	$q=SG\getFirst($q,trim($_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));

	if (!user_access('access saveup content')) return '[]';
	if (empty($q)) return '[]';

	list($name,$lname)=sg::explode_name(' ',$q);
//		if (empty($lname)) return '[]';

	$stmt='SELECT p.`mid`, CONCAT(p.`firstname`," ", p.`lastname`) name FROM %saveup_member% p
				WHERE `firstname` IS NOT NULL AND ((`firstname` LIKE :name '.($lname?'AND `lastname` LIKE :lname':'').') OR (p.mid LIKE :name))'.' 
				ORDER BY p.`firstname` ASC
				LIMIT '.($p-1).','.$n;

	$dbs=mydb::select($stmt,':name','%'.$name.'%',':lname','%'.$lname.'%');
	
	$result=array();
	foreach ($dbs->items as $rs) {
		$result[] = array(
									'value'=>$rs->mid,
									'label'=>htmlspecialchars($rs->mid.' '.$rs->name),
									'name'=>$rs->name
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