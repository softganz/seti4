<?php
/**
* QT Model :: Get QT Information
* Created 2020-10-25
* Modify  2020-10-25
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_qt_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	if (is_string($conditions) && preg_match('/^{/',$conditions)) {
		$conditions = SG\json_decode($conditions);
	} else if (is_object($conditions)) {
		//
	} else if (is_array($conditions)) {
		$conditions = (Object) $conditions;
	} else {
		$conditions = (Object) ['id' => $conditions];
	}

	if ($debug) debugMsg($conditions, '$conditions');
	if ($debug) debugMsg($options, '$options');

	$result = new sgClass();

	if ($conditions->id) mydb::where('q.`qtref` = :qtref',  ':qtref', $conditions->id);
	if ($conditions->tpid) mydb::where('q.`tpid` = :tpid', ':tpid', $conditions->tpid);
	if ($conditions->formId) mydb::where('q.`qtform` = :qtform', ':qtform', $conditions->formId);
	if ($conditions->date) mydb::where('q.`qtdate` = :qtdate', ':qtdate', $conditions->date);

	$stmt = 'SELECT q.* FROM %qtmast% q %WHERE% LIMIT 1';

	$rs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if (empty($rs->_num_rows)) return NULL;


	if (!$debug) mydb::clearprop($rs);

	$result->qtRef = $rs->qtref;
	$result->qtForm = $rs->qtform;
	$result->info = $rs;
	$result->tran = array();
	$result->header = array();
	$result->rate = array();

	$stmt = 'SELECT
		*
		FROM %qttran%
		WHERE `qtref` = :qtref
		ORDER BY `part` ASC;
		-- {key:"part"}
		';

	$result->tran = mydb::select($stmt, ':qtref', $result->qtRef)->items;
	if ($debug) debugMsg(mydb()->_query);

	$result->info->rates = $dbs->sum->rate;

	foreach ($result->tran as $rs) {
		if (substr($rs->part,0,5) == 'RATE.') {
			$result->rate[$rs->part] = $rs->rate;
			$result->info->rates += $rs->rate;
		}
		if (substr($rs->part,0,7) == 'HEADER.') $result->header[$rs->part] = $rs->value;
	}

	return $result;
}
?>