<?php
/**
* Model Name
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_ibuy_armas_get($conditions, $options = '{}') {
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
		*
		, CONCAT(X(`location`),",",Y(`location`)) `latlng`
		, X(`location`) `lat`
		, Y(`location`) `lnt`
		FROM %ARMAS%
		WHERE `CUSCOD` = :CUSCOD
		LIMIT 1';

	$rs = mydb::select($stmt, ':CUSCOD', $conditions->id);
	//debugMsg(mydb()->_query);

	if ($rs->_empty) return $result;

	$result->custid = $rs->CUSCOD;
	$result->custname = $rs->CUSNAM;
	$result->info = mydb::clearprop($rs);

	//debugMsg($result, '$result');

	return $result;
}
?>