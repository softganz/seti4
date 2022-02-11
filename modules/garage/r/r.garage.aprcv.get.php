<?php
/**
* Garage : Get AP Recieve Information
* Created 2017-08-24
* Modify  2020-10-21
*
* @param Int $rcvId
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_garage_aprcv_get($rcvId, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;
	
	$result = new sgClass();

	if (empty($rcvId)) return $result;

	$stmt = 'SELECT
		  r.*
		, i.`apname`
		, i.`apphone`
		, i.`apphone`
		, i.`apmail`
		, IFNULL(s.`shopparent`, r.`shopid`) `stklocid`
		FROM %garage_apmast% r
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_ap% i USING(`apid`)
		WHERE r.`rcvid` = :rcvid
		LIMIT 1';

	$result = mydb::select($stmt, ':rcvid',$rcvId);

	if ($result->count() == 0) return $result;

	if (!$debug) mydb::clearprop($result);
	$result->rcvTotal = 0;

	$stmt = 'SELECT
		s.*
		, rc.`repaircode` `stkcode`
		, rc.`repairname` `stkname`
		--	, j.*
		, j.`jobno`
		FROM %garage_stocktran% s
			LEFT JOIN %garage_repaircode% rc ON rc.`repairid` = s.`stkid`
			LEFT JOIN %garage_job% j USING(`tpid`)
		--	LEFT JOIN %garage_job% j USING(`tpid`)
		WHERE s.`shopid` = :shopid AND s.`refcode` = :refcode
		ORDER BY `stktrid` ASC;
		-- {key:"stktrid"}';

	$result->items = mydb::select($stmt,':shopid',$result->shopid,':refcode',$result->rcvno)->items;

	foreach ($result->qt as $item) $result->rcvTotal += $item->replyprice;

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>