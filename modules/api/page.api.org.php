<?php
function api_org($self,$q=NULL,$n=NULL,$p=NULL) {
	$q=SG\getFirst($q,trim(post('q')));
	$n=intval(SG\getFirst($item,post('n'),20));
	$p=intval(SG\getFirst($p,post('p'),1));
	$result=array();
	if (empty($q)) return $result;

	$getSector = post('sector');

	mydb::where('`name` LIKE :q',':q','%'.$q.'%');
	if ($getSector) mydb::where('o.`sector` = :sector', ':sector', $getSector);

	$stmt = 'SELECT o.`orgid`, o.`name`, o.`shortname`, o.`sector`
					FROM %db_org% o
					%WHERE%
					ORDER BY CONVERT(o.`name` USING tis620) ASC
				LIMIT '.($p-1).','.$n;

	$dbs = mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$desc = $rs->shortname ? $rs->shortname : '';

		$result[] = array(
										'value' => $rs->orgid,
										'label' => htmlspecialchars($rs->name),
										'orgid' => $rs->orgid,
										'desc' => htmlspecialchars($desc),
									);
	}
	if ($dbs->_num_rows>=$n) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');

	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return $result;
}
?>