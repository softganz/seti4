<?php
/**
 * Search from organization name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function project_get_activity($self,$tpid=NULL,$q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');
	$q=SG\getFirst($q,trim(post('q')));
	$n=intval(SG\getFirst($item,post('n'),20));
	$p=intval(SG\getFirst($p,post('p'),1));
	if (empty($q)) return '[]';

	$stmt='SELECT c.id, c.from_date,c.title,c.detail
		FROM %calendar% c
		WHERE c.tpid=:tpid && c.`title` LIKE :q
		ORDER BY c.from_date ASC
		LIMIT '.($p-1).','.$n;
	$dbs=mydb::select($stmt,':tpid',$tpid,':q','%'.$q.'%');

	$result=array();
	foreach ($dbs->items as $rs) {
		$result[] = array('value'=>$rs->id, 'label'=>htmlspecialchars($rs->title),'detail'=>$rs->detail,'date'=>sg_date($rs->from_date,'ว ดดด ปปปป'));
	}
	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return json_encode($result);
}
?>