<?php
/**
* Project API :: Planning Summary
* Created 2020-05-17
* Modify  2020-08-22
*
* @param Object $self
* @param Array $REQUEST
* @return JSON
*/
function project_api_follow_target($self) {
	$getDataType = SG\getFirst(post('dataType'),'json');
	$planSelect = SG\getFirst(post('pn'),NULL);
	$provinceSelect = SG\getFirst(post('pv'),NULL);
	$ampurSelect = SG\getFirst(post('am'), NULL);
	$areaSelect = SG\getFirst(post('ar'),NULL);
	$sectorSelect = SG\getFirst(post('s'),NULL);
	$yearSelect = post('yr');
	$planIdSelect = post('pnid');

	$isDebug = user_access('access debugging program') && post('debug');

	$filterPlan = SG\getFirst(post('plan'),implode(',',post('for_plan')));
	$filterArea = SG\getFirst(post('ar'),implode(',',post('for_area')));
	$filterChangwat = SG\getFirst(post('pv'),implode(',',post('for_changwat')));
	$filterAmpur = SG\getFirst(post('am'),implode(',',post('for_ampur')));
	$filterYear = SG\getFirst(post('yr'),implode(',',post('for_year')));
	$filterSector = SG\getFirst(post('s'),implode(',',post('for_sector')));


	$result->status = true;
	$result->count = 0;
	$result->title = '';
	$result->debug = $isDebug;

	$result->itemsFields = array(
		'plan' => 'แผนงาน',
		'amt' => 'จำนวนแผนงาน',
		'dev' => 'โครงการที่พัฒนา',
		'follow' => 'โครงการที่ติดตาม',
		'budget' => 'งบประมาณ',
	);

	if ($isDebug) {
		$result->process = array();
		$urlQueryString = post();
		array_shift($urlQueryString);
		$result->process[] = 'URL = '._DOMAIN.$_SERVER['REQUEST_URI'].'?'.sg_implode_attr($urlQueryString,'&', '{quote: ""}');
	}




	// Count Project Planning On Issue
	//mydb::where('tr.`formid` = "info" AND tr.`part` = "title" AND p.`prtype`="แผนงาน"');
	mydb::where('p.`prtype` = "โครงการ" AND f.`areaid` IS NOT NULL');

	if ($filterPlan) mydb::where('plan.`refid` IN ( :filterPlan )', ':filterPlan', 'SET:'.$filterPlan);
	if ($filterArea) mydb::where('f.`areaid` IN ( :filterArea )', ':filterArea', 'SET:'.$filterArea);
	if ($filterAmpur) {
		mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET-STRING:'.$filterAmpur);
	} else if ($filterChangwat) {
		mydb::where('o.`changwat` IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
	}
	if ($filterSector) mydb::where('o.`sector` IN ( :filterSector )', ':filterSector', 'SET:'.$filterSector);
	if ($filterYear) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);

	$stmt = 'SELECT
		f.`areaid`
		, a.`areaname`
		, p.`pryear`+543  `year`
		, COUNT(*) `target`
		, COUNT(DISTINCT tg.`tpid`) `projects`
		, SUM(p.`budget`) `budget`
		FROM %project_target% tg
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_fund% f ON f.`orgid`=t.`orgid`
			LEFT JOIN %project_area% a ON f.`areaid` = a.`areaid`
			LEFT JOIN %project_tr% plan ON plan.`tpid`=tg.`tpid` AND plan.`formid`="info" AND plan.`part`="supportplan" AND plan.`refid`=7
			LEFT JOIN %tag% tag ON tag.`taggroup`="project:target" and tag.`catid`=tg.`tgtid`
		%WHERE%
		GROUP BY `areaid`, p.`pryear`
		ORDER BY `areaid`,p.`pryear`
		-- PROJECT API FOLLOW TARGET;
		-- {sum:"target,projects,budget"}
		';

	$stmt = 'SELECT
		f.`areaid`
		, a.`areaname`
		, p.`pryear`+543  `year`
		-- , COUNT(*) `target`
		, SUM(tg.`amount`) `target`
		, COUNT(DISTINCT p.`tpid`) `projects`
		, SUM(p.`budget`) `budget`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
			LEFT JOIN %project_area% a ON f.`areaid` = a.`areaid`
			LEFT JOIN %project_tr% plan ON plan.`tpid` = p.`tpid` AND plan.`formid` = "info" AND plan.`part`="supportplan"
			LEFT JOIN %project_target% tg ON tg.`tpid` = p.`tpid` AND tg.`tagname` = "info"
			LEFT JOIN %tag% tag ON tag.`taggroup`="project:target" and tag.`catid`=tg.`tgtid`
		%WHERE%
		GROUP BY `areaid`, p.`pryear`
		ORDER BY `areaid`, p.`pryear`
		-- PROJECT API FOLLOW TARGET;
		-- {sum:"target,projects,budget"}
		';

	$stmt = 'SELECT
	b.*
	, COUNT(DISTINCT b.`tpid`) `projects`
	, SUM(b.`budget`) `budget`
	, SUM(b.`target`) `target`
	FROM (
		SELECT
			a.*
			, SUM(tg.`amount`) `target`
			FROM (
				SELECT
					p.`tpid`
					, f.`areaid`
					, a.`areaname`
					, p.`pryear`+543  `year`
					, p.`budget` `budget`
				FROM %project% p
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
					LEFT JOIN %project_area% a ON f.`areaid` = a.`areaid`
					LEFT JOIN %project_tr% plan ON plan.`tpid` = p.`tpid` AND plan.`formid` = "info" AND plan.`part`="supportplan"
				%WHERE%
				GROUP BY p.`tpid`
			) a
				LEFT JOIN %project_target% tg ON tg.`tpid` = a.`tpid` AND tg.`tagname` = "info"
			GROUP BY `tpid`
		) b
		GROUP BY `areaid`, `year`
		ORDER BY `areaid`, `year`
		-- PROJECT API FOLLOW TARGET;
		-- {sum:"target,projects,budget"}
		';

	$dbs = mydb::select($stmt);

	/*
	-- CHECK Target
	SELECT p.`tpid`,p.`pryear`, tg.`tpid`, tg.`tgtid`, tag.`name`, tg.`amount`
	FROM `sgz_project` p
		LEFT JOIN `sgz_project_target` tg ON tg.`tpid` = p.`tpid` AND tg.`tagname` = "info"
		LEFT JOIN `sgz_tag` tag ON tag.`taggroup`="project:target" and tag.`catid`=tg.`tgtid`
	WHERE p.`prtype` = "โครงการ" AND p.`pryear`=2021;

	-- CHECK project/budget
	SELECT p.`tpid`,p.`pryear`
	, COUNT(DISTINCT p.`tpid`) `projects` , SUM(p.`budget`) `budget`
	FROM `sgz_project` p
		LEFT JOIN `sgz_topic` t USING(`tpid`)
		LEFT JOIN `sgz_db_org` o USING(`orgid`)
		LEFT JOIN `sgz_project_fund` f ON f.`orgid`=t.`orgid`
		LEFT JOIN `sgz_project_area` a ON f.`areaid` = a.`areaid`
	WHERE p.`prtype` = "โครงการ" AND f.`areaid` IS NOT NULL AND p.`pryear`=2020
	*/

	if ($isDebug) $result->process[] = '<pre>'.mydb()->_query.'</pre>';
	//$ret .= print_o($planDbs,'$planDbs');
	//111,350

	$tables = new Table();
	$tables->thead = array(
		'area' => 'เขต',
		'year -date' => 'ปี',
		'follow -amt' => 'โครงการ',
		'budget -money' => 'งบประมาณ',
		'target -amt' => 'กลุ่มเป้าหมาย',
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'เขต '.$rs->areaid.' '.$rs->areaname,
			$rs->year,
			number_format($rs->projects),
			number_format($rs->budget,2),
			number_format($rs->target),
		);
	}

	$tables->tfoot[] = array(
		'',
		'',
		number_format($dbs->sum->projects),
		number_format($dbs->sum->budget,2),
		number_format($dbs->sum->target),
	);

	$result->html = $tables->build();


	//debugMsg(mydb()->_query); $result->query = mydb()->_query;

	$result->count = $dbs->_num_rows;
	$result->query = mydb()->_query;
	//debugMsg($result,'$result');
	//return sg_json_encode($result);
	return $getDataType == 'json' ? $result : $item->html;
}
?>