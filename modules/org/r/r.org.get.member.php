<?php
function r_org_get_member($orgid) {
	$members = (Object) [
		'count' => 0,
		'items' => [],
	];
	$stmt='SELECT
		mo.*
		, CONCAT(p.`prename`,p.`name`," ",p.`lname`) `personName`
		FROM %org_morg% mo
			LEFT JOIN %db_person% p USING(`psnid`)
		WHERE mo.`orgid` = :orgid';

	$dbs=mydb::select($stmt,':orgid',$orgid);

	if (!$dbs->_num_rows) return NULL;

	$members->count=$dbs->_num_rows;
	$members->items=$dbs->items;
	return $members;
}
?>