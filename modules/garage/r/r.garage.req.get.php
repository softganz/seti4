<?php
/**
* Garage : Get Requisition Information
* Created 2017-08-21
* Modify  2020-10-21
*
* @param Int $reqId
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_garage_req_get($reqId, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;
	
	$result = new sgClass();

	$stmt = 'SELECT
		  r.*
		, j.`jobno`
		, IFNULL(s.`shopparent`, r.`shopid`) `stklocid`
		FROM %garage_reqmast% r
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_job% j USING(`tpid`)
		WHERE `reqid` = :reqid
		LIMIT 1';

	$result = mydb::select($stmt, ':reqid',$reqId);

	if ($result->count() == 0) return $result;

	if (!$debug) mydb::clearprop($result);
	$result->costTotal = 0;

	$stmt = 'SELECT
		s.*
		, abs(s.`qty`) `qty`
		, abs(s.`price`) `price`
		, abs(s.`total`) `total`
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
		-- {key:"stktrid",sum:"total"}';

	$dbs = mydb::select($stmt,':shopid',$result->shopid,':refcode',$result->reqno);

	$result->items = $dbs->items;

	$result->costTotal = $dbs->sum->total;

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>