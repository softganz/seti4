<?php
/**
* Search for autocomplete
* 
* @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
* @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
* @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
* @return json[{value:org_id, label:org_name},...]
*/

$debug = true;

function icar_api_autocomplete($self, $fld = NULL) {
	sendheader('text/html');
	$q=SG\getFirst($q,trim($_GET['q'],$_POST['q']));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));


	if (empty($q)) return '[]';

	//foreach (mydb::select('SELECT DISTINCT `model` FROM %icar% ORDER BY `model` ASC')->items as $irs) $value[]=$irs->model;
	//print_o($value,'$value',1);

	$stmts['model']='SELECT DISTINCT `model` `label` FROM %icar% WHERE `model` LIKE :q ORDER BY `model` ASC;';

	$result=array();
	if ($stmts[$fld]) {
		$dbs=mydb::select($stmts[$fld],':q','%'.$q.'%');
		foreach ($dbs->items as $rs) {
			$result[] = array('value'=>$rs->id, 'label'=>htmlspecialchars($rs->label));
		}
		if (debug('api')) {
			$result[]=array('value'=>'query','label'=>$dbs->_query);
			if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
			$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
		}
	}
	return json_encode($result);
}
?>