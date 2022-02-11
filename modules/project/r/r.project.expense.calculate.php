<?php
/**
* Model :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("module.method", $condition, $options)
*/

$debug = true;

function r_project_expense_calculate($projectId, $actionId, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$dbs = mydb::select(
		'SELECT
		t.`name`, t.`catparent`, tr.`refid` `catid`
		, SUM(tr.`num1`) `amt`
		FROM %project_tr% tr
			LEFT JOIN %project_tr% a ON a.`formid` = "activity" AND a.`part` = "owner" AND a.`calid` = tr.`calid`
			LEFT JOIN %tag% t ON t.`taggroup` = "project:expcode" AND t.`catid` = tr.`refid`
		WHERE tr.`tpid` = :tpid AND tr.`formid` = "expense" AND tr.`part` = "exptr" AND a.`trid` = :tranId
		GROUP BY `catparent`;
		-- {key: "catparent"}
		',
		[':tpid' => $projectId, ':tranId' => $actionId]
	);

	$data = (Object) [
		'tranId' => $actionId,
		'num1' => SG\getFirst($dbs->items[1]->amt,0),
		'num2' => SG\getFirst($dbs->items[2]->amt,0),
		'num3' => SG\getFirst($dbs->items[3]->amt,0),
		'num4' => SG\getFirst($dbs->items[4]->amt,0),
		'num5' => SG\getFirst($dbs->items[5]->amt,0),
		'num6' => SG\getFirst($dbs->items[6]->amt,0),
	];
	$data->num7 = $data->num1 + $data->num2 + $data->num3 + $data->num4 + $data->num5 + $data->num6;

	mydb::query(
		'UPDATE %project_tr% SET
		  `num1` = :num1
		, `num2` = :num2
		, `num3` = :num3
		, `num4` = :num4
		, `num5` = :num5
		, `num6` = :num6
		, `num7` = :num7
		WHERE `trid` = :tranId
		LIMIT 1',
		$data
	);

	if ($debug) debugMsg(mydb()->_query);
	if ($debug) debugMsg($data,'$data');
	if ($debug) debugMsg($dbs,'$dbs');

	return $result;
}
?>