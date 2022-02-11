<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_fund_board_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['orgId' => $conditions];
	}


	mydb::where('b.`orgid` = :orgId AND `status` = 1', ':orgId', $conditions->orgId);
	if ($conditions->refId) mydb::where(' b.`refid` = :refId', ':refId', $conditions->refId);

	$dbs = mydb::select(
		'SELECT b.*, bp.`name` `boardName`, p.`name` `positionName`, p.`weight`
		FROM %org_board% b
			LEFT JOIN %tag% bp ON bp.`catid` = b.`boardposition` AND bp.`taggroup` = "project:board"
			LEFT JOIN %tag% p ON p.`taggroup` = "project:boardpos" AND p.`catid` = b.`position`
		%WHERE%
		ORDER BY `boardposition`, `weight`, `posno`'
	);

	$result = $dbs->items;
	return $result;
}
?>