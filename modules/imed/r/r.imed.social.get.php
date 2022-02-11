<?php
/**
* Organization Get
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_imed_social_get($conditions = NULL, $options = '{}') {
	$defaults='{debug: false, data: "*", resultType: "record", order: "CONVERT(o.`name` USING tis620) ASC", start: -1}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object)$conditions;
	else {
		$conditions = (Object) ['orgId' => $conditions];
	}

	$orgId = $conditions->orgId;

	if (empty($conditions->orgId)) return NULL;

	$result = NULL;

	$stmt = 'SELECT * FROM %imed_socialgroup% WHERE `orgid` = :orgid AND `status` > 0 LIMIT 1';
	$isServ = mydb::select($stmt, ':orgid',$orgId)->orgid;

	if (!$isServ) return $result;

	$result = R::Model('org.get', $conditions, $options);

	$result->is->socialtype = false;

	$stmt = 'SELECT * FROM %imed_socialmember% WHERE `orgid` = :orgid; -- {key: "uid"}';
	$members = mydb::select($stmt, ':orgid', $orgId)->items;


	$result->is->socialtype = array_key_exists(i()->uid, $members) ? $members[i()->uid]->membership : false;

	$result->is->admin = user_access('administer imeds') || $result->is->admin || $result->is->socialtype === 'ADMIN';

	if ($result->is->admin) $result->RIGHT = $result->RIGHT | _IS_ADMIN;
	$result->RIGHTBIN = decbin($result->RIGHT);

	if ($options->data == 'info') return $result;

	$result->members = $members;

	return $result;
}
?>