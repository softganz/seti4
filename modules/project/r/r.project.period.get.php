<?php
/**
* Project Model :: Get period information
* Created 2021-02-19
* Modify  2021-02-19
*
* @param Object $projectId
* @param Int $period
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("module.method", $condition, $options)
*/

$debug = true;

function r_project_period_get($projectId, $period = NULL, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	mydb::value('$LIMIT$', '');
	mydb::where('`tpid` = :projectId AND `formid` = "info" AND `part` = "period"', ':projectId', $projectId);

	if ($period) {
		mydb::where('`period` = :period', ':period', $period);
		mydb::value('$LIMIT$', 'LIMIT 1');
	}

	$stmt = 'SELECT
		t.`tpid` `projectId`, t.`trid`
		, t.`period`, t.`flag`, t.`uid`
		, t.`date1` `dateStart`
		, t.`date2` `dateEnd`
		, t.`detail1` `reportDateStart`
		, t.`detail2` `reportDateEnd`
		, t.`num1` `budget`
		, t.`num2` `paidAmt`
		, t.`detail3` `approveDate`
		, t.`detail4` `paidDate`
		, t.`refcode` `paidStatus`
		, t.`text1` `noteOwner`
		, t.`text2` `noteComplete`
		, t.`text3` `noteTrainer`
		, t.`text4` `noteGrant`
		, t.`text5` `noteSource`
		, t.`text6` `ownerTraining`
		, t.`text7` `ownerLearning`
		, t.`text8` `ownerNextPlan`
		, t.`created`, t.`modified`, t.`modifyby`
		, t.`data`
		FROM %project_tr% t
		%WHERE%
		ORDER BY `period` ASC
		$LIMIT$';

	$dbs = mydb::select($stmt);
	//debugMsg($dbs, '$dbs');


	/*
		data = '{userChecked, userApproved, dateApproved}
	*/

	if ($period) {
		$result = mydb::clearprop($dbs);
		$result = SG\json_decode($result, $result->data);
	} else {
		foreach ($dbs->items as $key => $rs) {
			$result[$rs->period] = $rs;
		}
	}

	return $result;
}
?>