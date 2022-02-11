<?php
/**
* Garage :: Get Shop Information
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_garage_get_shop($shopId = NULL, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$uid = i()->uid;

	$result = NULL;

	if ($shopId) {
		mydb::where('s.`shopid` = :shopId', ':shopId', $shopId);
	} else {
		mydb::where('gu.`uid` = :uid', ':uid', $uid);
	}
	$stmt = 'SELECT
		`shopid` `shopId`
		, s.*, gu.`uid`, u.`name`, NULL `iam`, NULL `position`
		FROM %garage_shop% s
			LEFT JOIN %garage_user% gu USING(`shopid`)
			LEFT JOIN %users% u USING(`uid`)
		%WHERE%
		LIMIT 1';

	$rs = mydb::select($stmt, ':uid', $uid);


	if ($rs->_empty) return $result;

	mydb::clearprop($rs);

	$shopId = $rs->shopid;

	$result = $rs;
	$result->branchId = NULL;
	$result->openJob = 0;
	$result->packageJobRemain = 0;
	$result->info = clone $rs;

	$result->info->stklocid = SG\getFirst($result->shopparent, $result->shopid);

	// Set shop package options
	$package->package = cfg('garage')->package->{$result->info->package};
	$shopOptions = SG\json_decode(
		$result->info->options,
		$package
	);
	$result->info->options = $shopOptions;

	$stmt = 'SELECT COUNT(*) `total` FROM %garage_job% WHERE `shopid` = :shopid AND `isjobclosed` = "No" LIMIT 1';

	$openJob = mydb::select($stmt, ':shopid', $shopId)->total;

	$result->openJob = $openJob;
	$result->packageJobRemain = $result->info->options->package->jobs - $openJob;

	//debugMsg('Job Open = '.$openJob.' Remain = '.$result->packageJobRemain);


	$stmt = 'SELECT `shopid`
		FROM %garage_shop%
		WHERE `shopid` = :shopid OR `shopparent` = :shopid;
		-- {key:"shopid"}';

	$branch = mydb::select($stmt, ':shopid', $result->shopid);

	$result->branch = array_keys($branch->items);
	$result->branchId = implode(',', $result->branch);

	$stmt = 'SELECT * FROM %garage_user% WHERE `shopid` = :shopid; -- {key: "uid"}';
	$result->member = mydb::select($stmt, ':shopid', $result->shopid)->items;
	$result->iam = strtoupper($result->member[$uid]->membership); 
	$result->position = $result->member[$uid]->position; 
	//debugMsg($result,'$result');
	return $result;
}
?>