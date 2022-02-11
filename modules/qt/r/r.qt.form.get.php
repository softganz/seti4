<?php
/**
* QT :: Get Form Information
* Created 2020-07-05
* Modify  2020-07-05
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_qt_form_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$stmt = 'SELECT f.* FROM %qtform% f WHERE f.`frmid` = :frmid LIMIT 1';
	$rs = mydb::select($stmt, ':frmid', $conditions->id);

	$result->formId = $rs->frmid;
	$result->name = $rs->name;

	$result->info = mydb::clearprop($rs);
	return $result;
}
?>