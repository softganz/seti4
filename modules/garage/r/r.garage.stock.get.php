<?php
function r_garage_stock_get($shopid,$stkid,$options='{}') {
	$defaults = '{debug:false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;
	$result = NULL;

	$stmt = 'SELECT
		  r.*
		FROM %garage_repaircode% r
		WHERE r.`repairid` = :stkid
		LIMIT 1';

	$result = mydb::select($stmt, ':stkid', $stkid);

	if (!$debug) mydb::clearprop($result);

	if ($result->count()) {
		$stmt = 'SELECT
			tr.*
			, FROM_UNIXTIME(tr.`created`,"%Y-%m-%d %H:%i:%s") `created`
			, j.`jobno`
			FROM %garage_stocktran% tr
				LEFT JOIN %garage_job% j USING(`tpid`)
			WHERE tr.`shopid` = :shopid AND `stkid` = :stkid
			ORDER BY
				`stkdate` ASC,
				CASE
					WHEN `qty` > 0 THEN 0
					WHEN `qty` <= 0 THEN 1
				END ASC,
				`stktrid` ASC;
			-- {key:"stktrid"}';

		$dbs = mydb::select($stmt,':shopid',$shopid,':stkid',$stkid);

		$result->items = $dbs->items;
		if ($debug) debugMsg(mydb()->_query);
	}

	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>