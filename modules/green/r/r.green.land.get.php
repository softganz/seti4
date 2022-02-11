<?php
/**
* Green Model :: Get Land Information
*
* @param Object $conditions
* @param Object $options
* @return Object
*
* $conditions $landId, {orgId: Int, me: Boolean}
*/

$debug = true;

function r_green_land_get($conditions, $options = '{}') {
	$defaults = '{debug: false, data: "", order: "l.`landid` DESC", limit: 1, start: -1}';
	$options = SG\json_decode($options, $defaults);
	$options->data = explode(',',$options->data);
	$debug = $options->debug;

	$result = (Object) [];

	if (is_string($conditions) && preg_match('/^\{/',$conditions)) {
		$conditions = SG\json_decode($conditions);
	} else if (is_object($conditions)) {
		// Do nothing
	} else if (is_array($conditions)) {
		$conditions = (Object) $conditions;
	} else {
		$conditions = (Object) ['lanId' => $conditions];
	}

	$landId = $conditions->landId;

	if ($debug) {
		debugMsg($conditions, '$conditions');
		debugMsg($options, '$options');
	}
	/*
	if ($landId) mydb::where('fp.`plantid` = :plantId', ':plantId', $landId);
	if ($conditions->shopId) mydb::where('fp.`orgid` = :shopId', ':shopId', $conditions->shopId);

	mydb::value('$ORDER$', $options->limit == 1 ? '' : 'ORDER BY '.$options->order, false);
	mydb::value('$LIMIT$', $options->limit == '*' ? '' : 'LIMIT '.$options->limit);

	$stmt = 'SELECT
		fp.*
		, m.`msgid`
		, o.`name` `shopName`
		, l.`landname` `landName`
		, u.`username`, u.`name` `ownerName`
		, tg.`name` `categoryName`
		, fp.`qty` - IFNULL((SELECT SUM(`qty`) FROM %ibuy_farmbook% WHERE `plantid` = fp.`plantid`),0) `balance`
		, IFNULL(
			(SELECT `file` FROM %topic_files% WHERE `orgid` = fp.`orgid` AND `tagname` = "GREEN,PLANT" AND `refid` = fp.`plantid` ORDER BY `fid` ASC LIMIT 1),
			(SELECT f.`file` FROM %msg% m LEFT JOIN %topic_files% f ON f.`tagname` = "GREEN,ACTIVITY" AND f.`refid` = m.`msgid` WHERE m.`plantid` = fp.`plantid` LIMIT 1)
			) `plantPhotos`
		FROM %ibuy_farmplant% fp
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %users% u ON u.`uid` = fp.`uid`
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %msg% m ON m.`plantid` = fp.`plantid`
			LEFT JOIN %tag% tg ON tg.`tid` = fp.`catid`
		%WHERE%
		$ORDER$
		$LIMIT$
		';
	*/

	if ($landId) mydb::where('l.`landid` = :landId', ':landId', $landId);
	if ($conditions->orgId) mydb::where('l.`orgid` = :orgId', ':orgId', $conditions->orgId);
	if ($conditions->me) mydb::where('l.`uid` = :uid', ':uid', i()->uid);

	mydb::value('$LIMIT$', $options->limit === '*' ? '' : 'LIMIT '.$options->limit);

	$stmt = 'SELECT
		l.*
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `location`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u USING(`uid`)
		%WHERE%
		$LIMIT$;
		-- {key: "landid"}';

	$dbs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($dbs->_empty) return NULL;

	if ($options->limit == 1) {
		$result->landId = $dbs->landid;
		$result->orgId = $dbs->orgid;
		$result->uid = $dbs->uid;
		$result->landName = $dbs->landname;

		$result->info = mydb::clearprop($dbs);

		$stmt = 'SELECT * FROM %topic_files% WHERE `tagname` = "GREEN,LAND" AND `refid` = :landId';
		$result->photos = mydb::select($stmt, ':landId', $landId)->items;

		if (in_array('orgInfo', $options->data)) {
			$result->orgInfo = R::Model('green.shop.get', $result->orgId);
		}

		if (in_array('plantInfo', $options->data)) {
			$stmt = 'SELECT
				p.*, l.`landname` `landName`
				, u.`username`, u.`name` `ownerName`
				, (SELECT `file` FROM %topic_files% f WHERE f.`tagname` LIKE "GREEN,%" AND f.`refid` = m.`msgid` ORDER BY f.`cover` DESC, f.`fid` ASC LIMIT 1) `coverPhoto`
				FROM %ibuy_farmplant% p
					LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
					LEFT JOIN %ibuy_farmland% l ON l.`landid` = p.`landid`
					LEFT JOIN %users% u ON u.`uid` = p.`uid`
				WHERE p.`landid` = :landid
				ORDER BY p.`startdate` DESC';

			$result->plantInfo = mydb::select($stmt, ':landid', $landId)->items;
		}

	} else {
		$result->totals = $dbs->count();
		$result->items = $dbs->items;
	}


	if ($debug) debugMsg($result, '$result');

	return $result;
}
?>