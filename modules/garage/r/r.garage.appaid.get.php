<?php
/**
* Garage : Get AP Paid Information
* Created 2017-12-27
* Modify  2020-10-21
*
* @param Int $paidId
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_garage_appaid_get($paidId, $options = '{}') {
	$defaults = '{debug:false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;
	
	$result = new sgClass();
	
	if (empty($paidId)) return $result;

	$stmt = 'SELECT
		b.*
		, i.`apname`
		FROM %garage_appaid% b
			LEFT JOIN %garage_ap% i USING(`apid`)
		WHERE b.`paidid` = :paidid
		LIMIT 1';

	$result = mydb::select($stmt, ':paidid',$paidId);

	if ($result->count() == 0) return $result;

	if (!$debug) mydb::clearprop($result);

	$stmt='SELECT
		a.*
		FROM %garage_apmast% a
		WHERE a.`paidid` = :paidid
		ORDER BY a.`rcvid` ASC;
		-- {key:"rcvid"}';

	$result->apmast = mydb::select($stmt,':paidid',$paidId)->items;

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>