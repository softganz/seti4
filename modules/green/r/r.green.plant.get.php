<?php
/**
* Green Model :: Get Plant Information
*
* @param Object $conditions
* @param Object $options
* @return Object
*/

$debug = true;

function r_green_plant_get($conditions, $options = '{}') {
	$defaults = '{debug: false, data: "",  order: "fp.`cropdate` DESC", limit: 1, start: -1}';
	$options = sg_json_decode($options, $defaults);
	$options->data = explode(',',$options->data);
	$debug = $options->debug;

	$result = (Object) [];

	if (is_string($conditions) && substr($conditions, 0, 1) == '{') {
		$conditions = SG\json_decode($conditions);
	} else if (is_object($conditions)) {
		// Do nothing
	} else if (is_array($conditions)) $conditions = (object)$conditions;
	else {
		$conditions = (Object) ['plantId' => $conditions];
	}

	$plantId = $conditions->plantId;

	if ($plantId) mydb::where('fp.`plantid` = :plantId', ':plantId', $plantId);
	if ($conditions->orgId) mydb::where('fp.`orgid` = :orgId', ':orgId', $conditions->orgId);

	mydb::value('$ORDER$', $options->limit == 1 ? '' : 'ORDER BY '.$options->order, false);
	mydb::value('$LIMIT$', $options->limit == '*' ? '' : 'LIMIT '.$options->limit);

	$stmt = 'SELECT
		fp.*
		, CONCAT(X(fp.`location`),",",Y(fp.`location`)) `location`
		, k.`name` `treeKind`
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
			LEFT JOIN %tag% k ON k.`taggroup` = "tree:kind" AND k.`catid` = fp.`catid`
		%WHERE%
		$ORDER$
		$LIMIT$;
		';
		/*
		, k.`name` `treeKind`
		, m.`msgid`
		, l.`landname` `landName`
		, l.`arearai`, l.`areahan`, l.`areawa`
		, l.`standard` `landStandard`
		, l.`approved` `landApproved`
		, l.`detail` `landDetail`
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `landLocation`
		FROM %ibuy_farmplant% p
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %users% u ON u.`uid` = p.`uid`
			LEFT JOIN %msg% m ON m.`tagname` = p.`tagname` AND m.`plantid` = p.`plantid`
			LEFT JOIN %tag% k ON k.`taggroup` = "tree:kind" AND k.`catid` = p.`catid`
		*/
	$dbs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($dbs->_empty) return NULL;

	if ($options->limit == 1) {
		$result->plantId = $dbs->plantid;
		$result->msgId = $dbs->msgid;
		$result->orgId = $dbs->orgid;
		$result->landId = $dbs->landid;
		$result->uid = $dbs->uid;
		$result->productName = $dbs->productname;

		$result->info = mydb::clearprop($dbs);

		$stmt = 'SELECT * FROM %topic_files% f
			WHERE f.`refid` = :msgId AND f.`tagname` IN ("GREEN,PLANT", "GREEN,RUBBER", "GREEN,TREE", "GREEN,ANIMAL")
			';
		$result->photos = mydb::select($stmt, ':msgId', $result->msgId)->items;

		if (in_array('orgInfo', $options->data)) {
			$result->orgInfo = R::Model('green.shop.get', $result->orgId);
		}

		if (in_array('animal', $options->data)) {
			$stmt = 'SELECT
				`bigid` `weightId`
				, `keyname` `tagname`
				, `keyid` `plantId`
				, `fldname`
				, `flddata`
				, `created`
				, `ucreated`
				, `modified`
				, `umodified`
				FROM %bigdata%
				WHERE `keyname` = "GREEN,ANIMAL" AND `keyid` = :keyid AND `fldname` = "weight"
				ORDER BY `created` ASC;
				-- {key: "weightId"}';
			$result->animalWeight = mydb::select($stmt, ':keyid', $result->plantId)->items;
			foreach ($result->animalWeight as $key => $value) {
				$result->animalWeight[$key] = SG\json_decode($value->flddata, $value);
			}
		}

	} else {
		$result->totals = $dbs->count();
		$result->items = $dbs->items;
	}


	if ($debug) debugMsg($result, '$result');

	return $result;
}
?>