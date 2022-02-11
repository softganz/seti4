<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_map_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$stmt = 'SELECT
					  m.*
					, u.`name`
					, n.`orgid`
					, CONCAT(X(`latlng`),",",Y(`latlng`)) `latlng`
					, X(`latlng`) lat
					, Y(`latlng`) lnt
					, o.`membership`
					FROM %map_networks% m
						LEFT JOIN %users% u USING(uid)
						LEFT JOIN %map_name% n USING(`mapgroup`)
						LEFT JOIN %org_officer% o ON o.`orgid` = n.`orgid` AND o.`uid` = :uid
					WHERE m.`mapid` = :mapid
					LIMIT 1';
	$rs = mydb::select($stmt, ':mapid', $conditions->id, ':uid',i()->uid);

	if ($rs->_empty) return NULL;

	if (!$debug) mydb::clearprop($rs);

	$result = $rs;


	$isEdit = user_access('administer maps','edit own maps content',$rs->uid)
						|| in_array($rs->membership, array('OFFICER','ADMIN','MANAGER'));

	$right = 0;
	if ($isEdit) $right = $right | _IS_EDITABLE;

	$result->RIGHT = $right;

	//debugMsg($result,'$result');
	return $result;
}
?>