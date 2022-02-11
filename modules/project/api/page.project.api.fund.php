<?php
/**
 * Search from fund
 * 
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function project_api_fund($self, $q = NULL, $n = NULL, $p = NULL) {
	sendheader('text/html');
	$q = trim(SG\getFirst($q,$_GET['q'],$_POST['q']));
	$n = intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p = intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	$retType = SG\getFirst(post('o'),'o');
	$getAmpur = post('ampur');

	if (empty($q) && empty($getAmpur)) return '[]';

	$result = array();
	
	if ($q) mydb::where('o.`shortname` LIKE :q OR o.`name` LIKE :q',':q','%'.$q.'%');
	if ($getAmpur) mydb::where('o.`areacode` LIKE :ampur', ':ampur', $getAmpur.'%');

	$stmt = 'SELECT o.`orgid`, f.`fundid`, o.`name`, o.`shortname`, f.`nameampur`, f.`namechangwat`
		FROM %project_fund% f
			LEFT JOIN %db_org% o ON o.`shortname`=f.`fundid`
		%WHERE%
		ORDER BY CONVERT(`name` USING tis620) ASC
		LIMIT '.($p-1).','.$n;

	$dbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);
	//debugMsg($dbs,'$dbs');
	

	foreach ($dbs->items as $rs) {
		$result[] = array(
			'value' => $retType == 'o' ? $rs->orgid : $rs->fundid,
			'label' => htmlspecialchars($rs->name),
			'desc' => htmlspecialchars($rs->shortname.' อ.'.$rs->nameampur.' จ.'.$rs->namechangwat),
		);
	}
	if ($dbs->_num_rows == $n) $result[] = array('value' => '...','label' => '+++ ยังมีอีก +++');
	if (debug('api')) {
		$result[] = array('value' => 'query', 'label' => $dbs->_query);
		$result[] = array('value' => 'num_rows', 'label' => 'Result is '.$dbs->_num_rows.' row(s).');
	}
	return $result;
}
?>