<?php
function r_org_get_joinpeople($orgid,$item=100,$start=0) {
	$joins = (Object) [];

	$dbs = mydb::select(
		'SELECT SQL_CALC_FOUND_ROWS mj.*, CONCAT(p.`prename`,p.`name`," ",p.`lname`) `personName` FROM %org_mjoin% mj LEFT JOIN %db_person% p USING(`psnid`) WHERE mj.`orgid`=:orgid LIMIT '.$start.','.$item,
		[':orgid' => $orgid]
	);

	$totals = $dbs->_found_rows;

	if ($dbs->_num_rows) {
		$joins->start=$start;
		$joins->item=$item;
		$joins->count=$dbs->_num_rows;
		$joins->allJoins=$totals;
		$joins->items=$dbs->items;
	}
	return $joins;
}
?>