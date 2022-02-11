<?php
/**
* Model Name
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_moneyback_get($tpid, $trid, $conditions = NULL, $options = '{}') {
	$defaults = '{clearProp:true, getAllRecord:false, debug:false}';
	$options = sg_json_decode($options,$defaults);
	$conditions = sg_json_decode($conditions);

	$result = new stdClass();

	mydb::where('(tr.`tpid` = :tpid AND tr.`formid` = "info" AND tr.`part` = "moneyback")', ':tpid', $tpid);
	if ($trid) mydb::where('tr.`trid` = :trid', ':trid', $trid);
	if ($conditions->refcode) mydb::where('tr.`detail2` = :refcode', ':refcode', $conditions->refcode);

	mydb::value('$LIMIT$', (empty($trid) && empty($conditions->refcode)) || $options->getAllRecord ? '; -- {key:"trid"}' : 'LIMIT 1', false);

	$stmt = 'SELECT
		  tr.`trid`
		, tr.`trid` `tranId`
		, tr.`tpid`, t.`orgid`, tr.`formid`, tr.`part`, tr.`uid`
		, tr.`date1` `rcvdate`
		, tr.`num1` `amount`
		, tr.`detail1` `no`
		, tr.`detail2` `refcode`
		, tr.`created`
		, u.`name` `posterName`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %users% u ON u.`uid` = tr.`uid`
		%WHERE%
		ORDER BY `rcvdate` ASC
		$LIMIT$';

	$rs = mydb::select($stmt);

	if ($rs->_num_rows) {
		if ($options->getAllRecord) $result=$rs->items;
		else $result=$options->clearProp?mydb::clearprop($rs):$rs;
	} else $result=null;

	if ($options->debug) {
		debugMsg(mydb()->_query);
		debugMsg($options,'$options');
		debugMsg($rs,'$rs');
	}
	return $result;
}
?>