<?php
/**
 * iMed API
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function imed_api_disabled($self,$q,$n,$p) {
	if (!user_access('access disabled dbs')) return false;
	sendheader('text/html');
	$q=SG\getFirst($q,trim($_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) return '[]';

	if (preg_match('/^(ตำบล)(.*)/',$q,$out)) {
		$where[]='p.tambon="'.addslashes($out[2]) .'"';
	} else if (preg_match('/^(อำเภอ)(.*)/',$q,$out)) {
		$where[]='p.ampur="'.addslashes($out[2]) .'"';
	} else if (strlen($q)>=2) {
		list($name,$lname)=sg::explode_name(' ',$q);
		$where[]='p.cid LIKE "%'.addslashes($name).'%" OR (p.name LIKE "'.addslashes($name).'%"'.($lname?' AND p.lname LIKE "'.addslashes($lname).'%"':'').')';
	} else {
		die;
	}
	$stmt='SELECT k.pid, p.name, p.lname, p.house, p.tambon, p.ampur, p.changwat
				FROM %imed_disabled% k
					LEFT JOIN %db_person% p ON p.psnid=k.pid
				WHERE '.implode(' OR ',$where).'
				ORDER BY p.`name` ASC
				LIMIT '.($p-1).','.$n;

	$dbs=mydb::select($stmt);

	$result=array();
	foreach ($dbs->items as $rs) {
		$result[] = array('value'=>$rs->pid, 'label'=>htmlspecialchars($rs->name.' '.$rs->lname));
	}
	if ($dbs->_num_rows==$n) $result[]=array('value'=>-1,'label'=>'มีอีก....');
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>