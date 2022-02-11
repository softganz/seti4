<?php
/**
* Organization Get
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_imed_pocenter_get($conditions = NULL, $options = '{}') {
	$defaults='{debug: false, resultType: "record", order: "CONVERT(o.`name` USING tis620) ASC", start: -1}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object)$conditions;
	else {
		$conditions = (Object) ['orgId' => $conditions];
	}

	$orgId = $conditions->orgId;

	if (empty($conditions->orgid)) return NULL;

	$result = NULL;

	$stmt = 'SELECT * FROM %org_service% WHERE `orgid` = :orgid AND `servname` = "POCENTER" LIMIT 1';
	$isServ = mydb::select($stmt, ':orgid',$orgid)->orgid;

	if (!$isServ) return $result;

	$result = R::Model('org.get', $conditions, $options);

	$socialMember = mydb::select('SELECT * FROM %imed_socialmember% WHERE `orgid` = :orgid', ':orgid', $orgid);

	foreach ($socialMember->items as $rs) $result->officers[$rs->uid] = $rs->membership;

	return $result;
}
?>