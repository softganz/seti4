<?php
/**
* Model Name
* Created 2019-01-01
* Modify  2019-01-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_code_ampur_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$id = $conditions->id;

	$stmt = 'SELECT
					  d.`distid` `id`
					, `distname` `name`
					, p.`provname` `changwatName`
					FROM %co_district% d
						LEFT JOIN %co_province% p ON p.`provid` = LEFT(d.`distid`,2)
					WHERE d.`distid` = :ampurId
					LIMIT 1;
					-- {reset: false}';

	$result = mydb::select($stmt, ':ampurId', $id);

	mydb::clearProp($result);

	if ($result->id) {
		$stmt = 'SELECT * FROM %co_subdistrict% WHERE LEFT(`subdistid`,4) = :ampurId; -- {key: "subdistid", reset: false}';
		$dbs = mydb::select($stmt, ':ampurId', $id);
		$result->tambon = new stdClass;
		$result->tambon->count = count($dbs->items);
		$result->tambon->list = $dbs->items;
	}

	//debugMsg(mydb()->_query);
	return $result;
}
?>