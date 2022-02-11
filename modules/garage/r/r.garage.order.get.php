<?php
/**
* Garage : Get Order Information
* Created 2017-08-24
* Modify  2020-10-21
*
* @param Int $orderId
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_garage_order_get($orderId, $options = '{}') {
	$defaults = '{debug:false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;
	
	$result = new sgClass();

	if (empty($orderId)) return $result;

	$stmt = 'SELECT
		  o.*
		, i.`apname`
		, i.`apphone`
		, i.`apphone`
		, i.`apmail`
		FROM %garage_ordmast% o
			LEFT JOIN %garage_ap% i USING(`apid`)
		WHERE o.`ordid` = :docid
		LIMIT 1';

	$result = mydb::select($stmt, ':docid',$orderId);

	if (!$debug) mydb::clearprop($result);

	if ($result->count() == 0) return $result;

	$stmt = 'SELECT
		s.*
		, rc.`repaircode` `stkcode`
		, rc.`repairname` `stkname`
		FROM %garage_ordtran% s
			LEFT JOIN %garage_repaircode% rc ON rc.`repairid` = s.`stkid`
		WHERE s.`ordid` = :docid
		ORDER BY s.`ordtrid` ASC;
		-- {key:"ordtrid"}';

	$result->items = mydb::select($stmt, ':docid',$orderId)->items;

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>