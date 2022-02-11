<?php
/**
* Garage :: Get Job Information
* Created 2019-01-23
* Modify  2021-03-20
*
* @param Int/Object $shopId
* @param Int $jobId
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("garage.job.get", $shopId, $jobId, $options)
*/

$debug = true;

function r_garage_job_get($shopId, $jobId, $options = '{}') {
	$defaults = '{value:"repairname",debug:false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$shopInfo = NULL;

	if (is_object($shopId)) {
		$shopInfo = $shopId;
		$shopId = $shopInfo->shopid;
	}

	// debugMsg($shopInfo,'$shopInfo');

	$result = NULL;

	mydb::where('j.`tpid` = :jobId', ':jobId', $jobId);
	//mydb::where('(j.`shopid` = :shopId OR j.`shopid` = :shopParentId)', ':shopId', $shopId, ':shopParentId', $shopInfo->shopparent);
	$stmt = 'SELECT
			j.`tpid` `jobId`
		, j.`shopid` `shopId`
		, j.*
		, u.`name` `rcvbyName`
		, c.`customername`
		, c.`customerphone`
		, i.`insurername`
		, tp.`templatename`
		, s.`shopparent`
		, s.`shortname` `shopShortName`
		, (SELECT COUNT(*) FROM %garage_jobtr% WHERE `tpid` = :jobId AND `wait` > 0) `waitPart`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %users% u ON u.`uid` = j.`rcvby`
			LEFT JOIN %garage_jobtemplate% tp USING(`templateid`)
			LEFT JOIN %garage_customer% c USING(`customerid`)
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
		%WHERE%
		LIMIT 1';

	$result = mydb::select($stmt);
	//debugMsg(mydb()->_query);

	if (empty($result->_num_rows)) return NULL;
	if (!$debug) mydb::clearprop($result);

	$jobShopInfo = R::Model('garage.get.shop', $result->shopId);

	$result->replyprice = 0;
	$result->totalCost = 0;
	$result->totalWage = 0;

	$result->is = NULL;
	$result->is->editable = in_array($result->shopId, $shopInfo->branch) && R::Model('garage.right', $shopInfo, 'JOB');
	$result->is->viewable = in_array($result->shopId, $shopInfo->branch);
	$result->is->closed = strtoupper($result->isjobclosed) == 'YES';


	$result->shopInfo = $jobShopInfo;
	$result->command = array();
	$result->part = array();
	$result->wage = array();


	$stmt = 'SELECT d.*, u.`username`, u.`name`
		FROM %garage_do% d
			LEFT JOIN %users% u USING(`uid`)
		WHERE `tpid` = :jobId; -- {group: "dotype", key: "uid"}';

	$result->do = mydb::select($stmt, ':jobId', $jobId)->items;

	$stmt = 'SELECT
			  tr.*
			, rc.`repairtype`
			, rc.`repaircode`
			, IF(tr.`description` != "",tr.`description`,rc.`repairname`) `repairname`
			, 0 `photoCount1`
			, 0 `photoCount2`
			, 0 `photoCount3`
			, 0 `photoCount4`
			, 0 `photoCount5`
			, 0 `photoCount6`
		--	, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = tr.`jobtrid` AND `tagname` = "garage,photo1") `photoCount1`
		--	, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = tr.`jobtrid` AND `tagname` = "garage,photo2") `photoCount2`
		--	, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = tr.`jobtrid` AND `tagname` = "garage,photo3") `photoCount3`
		--	, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = tr.`jobtrid` AND `tagname` = "garage,photo4") `photoCount4`
		--	, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = tr.`jobtrid` AND `tagname` = "garage,photo5") `photoCount5`
		--	, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = tr.`jobtrid` AND `tagname` = "garage,photo6") `photoCount6`
		FROM %garage_jobtr% tr
			LEFT JOIN %garage_repaircode% rc USING(`repairid`)
		WHERE tr.`tpid` = :jobId
		ORDER BY `repairtype` ASC, tr.`sorder` ASC;
		-- {key:"jobtrid"}';

	/*
	// Improve Query @2020-08-22
	$stmt1 = 'SELECT
		a.*
		, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = a.`jobtrid` AND `tagname` = "garage,photo1") `photoCount1`
		, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = a.`jobtrid` AND `tagname` = "garage,photo2") `photoCount2`
		, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = a.`jobtrid` AND `tagname` = "garage,photo3") `photoCount3`
		, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = a.`jobtrid` AND `tagname` = "garage,photo4") `photoCount4`
		, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = a.`jobtrid` AND `tagname` = "garage,photo5") `photoCount5`
		, (SELECT COUNT(*) FROM %topic_files% WHERE `refid` = a.`jobtrid` AND `tagname` = "garage,photo6") `photoCount6`
		FROM
		(SELECT
			  tr.*
			, rc.`repairtype`
			, rc.`repaircode`
			, IF(tr.`description` != "",tr.`description`,rc.`repairname`) `repairname`
		FROM %garage_jobtr% tr
			LEFT JOIN %garage_repaircode% rc USING(`repairid`)
		WHERE tr.`tpid` = :jobId
		ORDER BY `repairtype` ASC, tr.`sorder` ASC
		) a
		ORDER BY `repairtype` ASC, `sorder` ASC
		;
		-- {key:"jobtrid"}';
	*/

	$jobTranDbs = mydb::select($stmt, ':jobId', $jobId);
	//debugMsg(mydb()->_query);
	//debugMsg($jobTranDbs,'jobTranDbs');

	// Improve Query on Photo Count of Each Transaction @2020-08-25
	$stmt = 'SELECT *
		FROM %topic_files%
		WHERE `tpid` = :jobId AND `type` = "photo" AND `tagname` LIKE "garage,%";
		-- {group: "tagname"}';
	$jobPhotoDbs = mydb::select($stmt, ':jobId', $jobId);

	foreach ($jobPhotoDbs->items as $tagname => $photoList) {
		switch ($tagname) {
			case 'garage,photo1': $photoField = 'photoCount1'; break;
			case 'garage,photo2': $photoField = 'photoCount2'; break;
			case 'garage,photo3': $photoField = 'photoCount3'; break;
			case 'garage,photo4': $photoField = 'photoCount4'; break;
			case 'garage,photo5': $photoField = 'photoCount5'; break;
			case 'garage,photo6': $photoField = 'photoCount6'; break;
			default: $photoField = NULL; break;
		}
		if (!$photoField) continue;
		foreach ($photoList as $item) {
			if ($jobTranDbs->items[$item->refid]) {
				$jobTranDbs->items[$item->refid]->{$photoField}++;
			}
		}
	}


	foreach ($jobTranDbs->items as $item) {
		if ($item->repairtype == 1) {
			$result->command[$item->jobtrid] = $item;
			$result->totalservice += $item->totalsale;
		} else if ($item->repairtype == 2) {
			$result->part[$item->jobtrid] = $item;
			$result->totalpart += $item->totalsale;
		} else if ($item->repairtype == 3) {
			$result->wage[$item->jobtrid] = $item;
			$result->totalWage += $item->totalsale;
		}
	}
	//debugMsg(mydb()->_query);

	// Get QT
	$stmt = 'SELECT q.*, i.`insurername`, b.`billno`, b.`billdate`, r.`rcvno`
		FROM %garage_qt% q
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
			LEFT JOIN %garage_billing% b USING(`billid`)
			LEFT JOIN %garage_rcv% r USING(`rcvid`)
		WHERE q.`tpid` = :jobId
		ORDER BY `qtid` ASC;
		-- {key:"qtid"}';

	$result->qt = mydb::select($stmt,':jobId',$jobId)->items;

	$replyprice=0;
	foreach ($result->qt as $item) {
		$replyprice += $item->replyprice;
		if ($item->billid) $result->billing[$item->billid] = $item->billno;
		if ($item->rcvid) $result->recieve[$item->rcvid] = $item->rcvno;
	}
	$result->replyprice = $replyprice;

	// Get cost
	$stmt = 'SELECT
		s.*
		, ABS(s.`total`) `total`
		, r.`repaircode` `stkcode`, r.`repairname` `stkname`
		, a.`refno`
		, a.`rcvid`
		, rq.`reqid`
		FROM %garage_stocktran% s
			LEFT JOIN %garage_repaircode% r ON  r.`repairid` = s.`stkid`
			LEFT JOIN %garage_apmast% a ON a.`shopid` = s.`shopid` AND a.`rcvno` = s.`refcode`
			LEFT JOIN %garage_reqmast% rq ON rq.`shopid` = s.`shopid` AND rq.`reqno` = s.`refcode`
		WHERE s.`tpid` = :jobId;
		-- {key:"stktrid", sum:"total"}';

	$dbs = mydb::select($stmt,':jobId',$jobId);

	$result->cost = array();

	if ($dbs->_num_rows) {
		$result->totalCost = $dbs->sum->total;
		$result->cost = $dbs->items;
	}

	$result->photos = $jobPhotoDbs;

	if ($debug) debugMsg($result,'$result');
	return $result;
}
?>