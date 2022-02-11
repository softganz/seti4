<?php
/**
* iBuy Get Plant Information
*
* @param Object $conditions
* @param Object $options
* @return Object
*/

$debug = true;

function r_ibuy_land_get($conditions, $options = '{}') {
	$defaults = '{debug: false, order: "p.`plantid` DESC", limit: 1, start: -1}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object)$conditions;
	else {
		$conditions = (Object) ['landId' => $conditions];
	}

	$landId = $conditions->landId;

	mydb::where('l.`landid` = :landId', ':landId', $landId);

	$stmt = 'SELECT
		l.*
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(`location`),",",Y(`location`)) `location`
		, X(`location`) `lat`
		, Y(`location`) `lnt`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u USING(`uid`)
		%WHERE%
		LIMIT 1';

	$rs = mydb::select($stmt);

	if ($rs->_empty) return $result;

	$result->landId = $rs->landid;
	$result->shopId = $rs->orgid;
	$result->landName = $rs->landname;
	$result->uid = $rs->uid;

	$result->info = mydb::clearprop($rs);


	mydb::where('fp.`landid` = :landId', ':landId', $landId);
	$stmt = 'SELECT
				fp.*
			, o.`name` `shopName`
			, tg.`name` `categoryName`
			, fp.`qty` - IFNULL((SELECT SUM(`qty`) FROM %ibuy_farmbook% WHERE `plantid` = fp.`plantid`),0) `balance`
			FROM %ibuy_farmplant% fp
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %tag% tg ON tg.`tid` = fp.`catid`
			%WHERE%
			LIMIT 1';

	$dbs = mydb::query($stmt);

	$result->plant = $dbs->items;

	$stmt = 'SELECT * FROM %topic_files% WHERE `tagname` = "ibuy,plant" AND `refid` = :plantId';
	$result->photos = mydb::select($stmt, ':plantId', $plantId)->items;


	if ($debug) debugMsg($result, '$result');

	return $result;
}
?>