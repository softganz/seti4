<?php
/**
 * Search from meeting calendar
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function org_api_org($self,$q=NULL,$n=NULL,$p=NULL) {
	sendheader('text/html');
	$q=SG\getFirst(trim($q),trim(post('q')));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));
	if (empty($q)) return '[]';

	$sector = post('sector');
	$getParent = post('parent');
	$getOnlyHasEnShortName = post('enShortName');


	mydb::where('(o.`name` LIKE :q OR o.`shortname` LIKE :q OR o.`enshortname` LIKE :q)', ':q', '%'.$q.'%');
	if ($sector == 'other') mydb::where('`sector` <> 1');
	else if ($sector) mydb::where('o.`sector` = :sector', ':sector', $sector);
	if ($getParent == 'none') mydb::where('o.`parent` IS NULL');
	if ($getOnlyHasEnShortName) mydb::where('o.`enShortName` != ""');


	$stmt = 'SELECT o.`orgid`, o.`name`, o.`shortName`, o.`enShortName`
		FROM %db_org% o
		%WHERE%
		ORDER BY CONVERT(o.`name` USING tis620) ASC
		LIMIT '.($p-1).','.$n;

	$dbs = mydb::select($stmt);

	$result = [];

	foreach ($dbs->items as $rs) {
		$desc = $rs->shortName
			. ($rs->shortName && $rs->enShortName ? '/' : '')
			. ($rs->enShortName ? $rs->enShortName : '')
			. ($rs->address?' : '.$rs->address:'');

		$result[] = [
			'value' => $rs->orgid,
			'label' => htmlspecialchars($rs->name),
			'shortname' => htmlspecialchars($rs->shortName.($rs->enShortName ? $rs->enShortName : '')),
			'desc' => htmlspecialchars($desc),
		];
	}
	if ($dbs->_num_rows>=$n) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');
	if (debug('api')) {
		$result[]=array('value'=>'sector','label'=>$sector);
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	if (debug('html')) return print_o($result,'$result');
	return sg_json_encode($result);
}
?>