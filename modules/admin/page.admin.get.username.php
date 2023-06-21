<?php
/**
 * Search from username and name
 * 
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function admin_get_username($self,$q='',$n=NULL,$p=NULL) {
	sendheader('text/html');
	$q = SG\getFirst($q,trim(post('q')));
	$n=intval(\SG\getFirst($item,post('n'),20));
	$p=intval(\SG\getFirst($p,post('p'),1));
	$retType = SG\getFirst(post('r'),'u');
	if (empty($q)) return '[]';
	
	$stmt='SELECT `uid`, `username`, `name`, `email`, `datein`
					FROM %users%
					WHERE `username` LIKE :q OR `name` LIKE :q OR `email` LIKE :q
					ORDER BY `name` ASC
					LIMIT '.($p-1).','.$n;
	$dbs=mydb::select($stmt,':q','%'.$q.'%');
	
	$result=array();
	foreach ($dbs->items as $rs) {
		$result[] = array(
									'value'=>$retType=='id'?$rs->uid:$rs->username,
									'label'=>htmlspecialchars($rs->name),
									'desc'=>$rs->username.' ('.$rs->uid.') '.$rs->email.' @'.sg_date($rs->datein,'d/m/Y')
								);
	}
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>