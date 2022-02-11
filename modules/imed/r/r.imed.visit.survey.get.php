<?php
/**
* iMed Model :: Get Visit Survey Information
* Created 2020-12-12
* Modify  2020-12-12
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_imed_visit_survey_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	if ($debug) debugMsg($options, '$options');

	$result = NULL;

	if (is_string($conditions) && preg_match('/^\{/',$conditions)) {
		$conditions = SG\json_decode($conditions);
	} else if (is_object($conditions)) {
		//
	} else if (is_array($conditions)) {
		$conditions = (object) $conditions;
	} else {
		$conditions = (Object) ['surveyId' => $conditions];
	}

	if ($debug) debugMsg($conditions, '$conditions');

	if ($conditions->surveyId) mydb::where('`qtref` = :surveyId', ':surveyId', $conditions->surveyId);
	if ($conditions->formId) mydb::where('q.`qtform` = :qtform', ':qtform', $conditions->formId);
	if ($conditions->psnId) mydb::where('q.`psnid` = :psnid', ':psnid', $conditions->psnId);
	if ($conditions->seqId) mydb::where('q.`seq` = :seq', ':seq', $conditions->seqId);

	$stmt = 'SELECT
		q.*
		, FROM_UNIXTIME(`created`) `created`
		FROM %qtmast% q
		%WHERE%
		LIMIT 1';

	$rs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($rs->_empty) return $result;

	$result->qtRef = $rs->qtref;
	$result->uid = $rs->uid;
	$result->info = mydb::clearprop($rs);
	$result->data = SG\json_decode($rs->data);

	mydb::where('tr.`qtref` = :qtRef', ':qtRef', $result->qtRef);
	$stmt = 'SELECT tr.*
		FROM %qttran% tr
		%WHERE%
		;
		-- {key: "part"}
		';

	$result->trans = mydb::select($stmt)->items;

	if ($debug) debugMsg(mydb()->_query);

	/*
	if (array_key_exists('JSON', $result->trans)) {
		$result->data = SG\json_decode($result->trans['JSON']->value);
		$result->data->qtid = $result->trans['JSON']->qtid;
		$result->data->rate = $result->trans['JSON']->rate;
	}
	*/

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>