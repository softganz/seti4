<?php
/**
* Model :: Description
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("module.method", $condition, $options)
*/

$debug = true;

function r_bmc_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	mydb::where('`bmcid` = :bmcid', ':bmcid', $conditions->id);
	$stmt = 'SELECT * FROM %bmc%
		%WHERE%
		LIMIT 1';

	$rs = mydb::select($stmt);

	$result->bmcId = $rs->bmcid;
	$result->uid = $rs->uid;
	$result->title = $rs->title;
	$result->info = $rs;

	return $result;
}
?>