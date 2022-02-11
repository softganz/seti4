<?php
/**
* Project :: API of planning situation
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Array $param
* @return JSON
*
* @usage project/api/planning/situations
*/

$debug = true;

function project_api_planning_situations_summary($self, $param = NULL) {
	$getGroupBy = SG\getFirst(post('group'),'year');

	$filterPlan = SG\getFirst($param->filterPlan, post('plan'), is_array(post('for_plan')) ? implode(',',post('for_plan')) : NULL);
	$filterProblem = SG\getFirst($param->filterProblem, post('problem'), is_array(post('for_problem')) ? implode(',',post('for_problem')) : NULL);
	$filterArea = SG\getFirst($param->filterArea, post('area'), is_array(post('for_area')) ? implode(',',post('for_area')) : NULL);
	$filterChangwat = SG\getFirst($param->filterChangwat, post('changwat'), is_array(post('for_changwat')) ? implode(',',post('for_changwat')) : NULL);
	$filterAmpur = SG\getFirst($param->filterAmpur, post('ampur'), is_array(post('for_ampur')) ? implode(',',post('for_ampur')) : NULL);
	$filterFund = SG\getFirst($param->filterFund, post('fund'), is_array(post('for_fund')) ? implode(',',post('for_fund')) : NULL);

	$filterYear = SG\getFirst($param->filterYear, post('year'), is_array(post('for_year')) ? implode(',',post('for_year')) : NULL);
	$filterSector = SG\getFirst($param->filterSector, post('sector'), is_array(post('for_sector')) ? implode(',',post('for_sector')) : NULL);
	list($filterProblem) = explode(':', $filterProblem);

	$isDebug = user_access('access debugging program') && post('debug');

	header('Access-Control-Allow-Origin: *');
	//$headerResult = http_response_code(200);

	$groupBy = [
		'problem' => '`problemId`',
		'year/problem' => '`year`,`problemId`',
	][$getGroupBy];
	if (!$groupBy) $groupBy = '`year`';

	$result = (Object) [
		'status' => true,
		'count' => 0,
		'debug' => $isDebug,
		'parameter' => (Object) [],
		'summaryFields' => [
			'label' => 'สถานการณ์',
			'problemAvg' => 'ค่าเฉลี่ยขนาดปัญหา',
			'targetAvg' => 'ค่าเฉลี่ยเป้าหมาย',
			'planAmt' => 'จำนวนแผนงาน',
		],
		'summary' => [],
	];

	if ($filterPlan) $result->parameter->plan = $filterPlan;
	if ($filterProblem) $result->parameter->problem = $filterProblem;
	if ($filterArea) $result->parameter->area = $filterArea;
	if ($filterChangwat) $result->parameter->changwat = $filterChangwat;
	if ($filterAmpur) $result->parameter->ampur = $filterAmpur;
	if ($filterYear) $result->parameter->year = $filterYear;
	if ($filterFund) $result->parameter->fund = $filterFund;
	if ($filterType) $result->parameter->type = $filterType;

	if ($filterArea) $result->summaryFields['areaProblem'] = 'เขต '.$filterArea;
	if ($filterChangwat) {
		$result->summaryFields['changwatProblem'] = 'จังหวัด'
			. mydb::select('SELECT * FROM %co_province% WHERE `provid` = :changwat LIMIT 1', ':changwat', $filterChangwat)->provname;
	}
	if ($filterAmpur) {
		$result->summaryFields['ampurProblem'] = 'อำเภอ'
			. mydb::select('SELECT * FROM %co_district% WHERE `distid` = :ampur LIMIT 1', ':ampur', $filterAmpur)->distname;
	}
	if ($filterFund) {
		$result->summaryFields['fundProblem'] = mydb::select('SELECT * FROM %db_org% WHERE `orgid` = :orgid LIMIT 1', ':orgid', $filterFund)->name;
	}


	if ($isDebug && !$param) {
		$result->process = [];
		$urlQueryString = post();
		array_shift($urlQueryString);
		$result->process[] = 'URL = '._DOMAIN.$_SERVER['REQUEST_URI'].'?'.sg_implode_attr($urlQueryString,'&', '{quote: ""}');
		$result->process[] = print_o(post(),'post()');
	}

	$totalList = [];
	$join = [];
	$valuationCondition = $filterValuation ? ' AND v.`part` LIKE :part' : '';


	// Filter
	mydb::where('( t.`orgid` IS NOT NULL AND p.`prtype` = "แผนงาน" AND pg.`process` > 0 AND (tr.`formid` LIKE "info" AND tr.`part` LIKE :part AND tr.`refid` IS NOT NULL AND tr.`num1` IS NOT NULL AND tr.`num2` IS NOT NULL) )', ':part', "problem");
	if ($filterPlan) mydb::where('problem.`refid` IN ( :planningId )', ':planningId', 'SET:'.$filterPlan);
	if ($filterProblem) mydb::where('tr.`refid` IN ( :problemId )', ':problemId', 'SET:'.$filterProblem);
	if ($filterYear) mydb::where('p.`pryear` = :year', ':year', $filterYear);

	// Average
	$totalList[] = 'CAST(AVG(tr.`num1`) AS DECIMAL(12,2)) `countryProblem`';
	$totalList[] = 'CAST(AVG(tr.`num2`) AS DECIMAL(12,2)) `countryTarget`';
	$totalList[] = 'COUNT(DISTINCT p.`tpid`) `countryPlan`';
	if ($filterArea) {
		$totalList[] = 'CAST(AVG(IF(f.`areaid` = :areaId, tr.`num1`, NULL)) AS DECIMAL(12,2)) `areaProblem`';
		$totalList[] = 'CAST(AVG(IF(f.`areaid` = :areaId, tr.`num2`, NULL)) AS DECIMAL(11,2)) `areaTarget`';
		$totalList[] = 'COUNT(DISTINCT IF(f.`areaid` = :areaId, p.`tpid`, NULL)) `areaPlan`';
		mydb::where(NULL,':areaId', $filterArea);
		$join[] = 'LEFT JOIN %project_fund% f ON f.`orgid` = o.`orgid`';
	}
	if ($filterChangwat) {
		$totalList[] = 'CAST(AVG(IF(t.`areacode` LIKE :changwat, tr.`num1`, NULL)) AS DECIMAL(12,2)) `changwatProblem`';
		$totalList[] = 'CAST(AVG(IF(t.`areacode` LIKE :changwat, tr.`num2`, NULL)) AS DECIMAL(12,2)) `changwatTarget`';
		$totalList[] = 'COUNT(DISTINCT IF(t.`areacode` LIKE :changwat, p.`tpid`, NULL)) `changwatPlan`';
		mydb::where(NULL,':changwat', $filterChangwat.'%');
	}
	if ($filterAmpur) {
		$totalList[] = 'CAST(AVG(IF(t.`areacode` LIKE :ampur, tr.`num1`, NULL)) AS DECIMAL(12,2)) `ampurProblem`';
		$totalList[] = 'CAST(AVG(IF(t.`areacode` LIKE :ampur, tr.`num2`, NULL)) AS DECIMAL(12,2)) `ampurTarget`';
		$totalList[] = 'COUNT(DISTINCT IF(t.`areacode` LIKE :ampur, p.`tpid`, NULL)) `ampurPlan`';
		mydb::where(NULL,':ampur', $filterAmpur.'%');
	}
	if ($filterFund) {
		$totalList[] = 'CAST(AVG(IF(t.`orgid` = :fund, tr.`num1`, NULL)) AS DECIMAL(12,2)) `fundProblem`';
		$totalList[] = 'CAST(AVG(IF(t.`orgid` = :fund, tr.`num2`, NULL)) AS DECIMAL(12,2)) `fundTarget`';
		$totalList[] = 'COUNT(DISTINCT IF(t.`orgid` = :fund, p.`tpid`, NULL)) `fundPlan`';
		mydb::where(NULL,':fund', $filterFund);
	}

	/*
	if ($filterArea) mydb::where('f.`areaid` IN ( :areaid )', ':areaid', 'SET:'.$filterArea);
		if ($filterAmpur) {
		mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET-STRING:'.$filterAmpur);
	} else if ($filterChangwat) {
		mydb::where('o.`changwat` IN ( :changwat )', ':changwat', 'SET:'.$filterChangwat);
	}
	if ($filterYear) mydb::where('p.`pryear` IN ( :year )', ':year', 'SET:'.$filterYear);
	if ($filterSector) mydb::where('o.`sector` IN ( :sector )', ':sector', 'SET:'.$filterSector);
	*/

	mydb::value('$FIELD$', ($totalList ? ', ' : '').implode(_NL.'		, ', $totalList), false);
	mydb::value('$JOIN$', implode(_NL, $join));
	mydb::value('$GROUPBY$', $groupBy);

	$stmt = 'SELECT
		   p.`pryear` `year`
		, problem.`refid` `planGroupId`
		, tr.`refid` `problemId`
		, pg.`name` `situationName`
		$FIELD$
		-- , p.`tpid`, p.`prtype`
		-- , tr.`num1` `problemSize`
		-- , tr.`num2` `targetSize`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			$JOIN$
			LEFT JOIN %project_tr% problem ON problem.`tpid` = tr.`tpid` AND problem.`formid`="info" AND problem.`part` = "title"
			LEFT JOIN %tag% pg ON pg.`taggroup` = CONCAT("project:problem:",problem.`refid`) AND pg.`catid` = tr.`refid`
		%WHERE%
		GROUP BY $GROUPBY$
		ORDER BY `year` ASC, pg.`weight`, `problemId` ASC
		';
	$dbs = mydb::select($stmt);
	if ($isDebug) $result->process[] = '<pre>'.mydb()->_query.'</pre>';
	//debugMsg(mydb()->_query);

	$result->count = $dbs->count();

	foreach ($dbs->items as $rs) {
		if (isset($rs->countryProblem)) $rs->countryProblem = floatval($rs->countryProblem);
		if (isset($rs->countryTarget)) $rs->countryTarget = floatval($rs->countryTarget);
		if (isset($rs->areaProblem)) $rs->areaProblem = floatval($rs->areaProblem);
		if (isset($rs->areaTarget)) $rs->areaTarget = floatval($rs->areaTarget);
		if (isset($rs->changwatProblem)) $rs->changwatProblem = floatval($rs->changwatProblem);
		if (isset($rs->changwatTarget)) $rs->changwatTarget = floatval($rs->changwatTarget);
		if (isset($rs->ampurProblem)) $rs->ampurProblem = floatval($rs->ampurProblem);
		if (isset($rs->ampurTarget)) $rs->ampurTarget = floatval($rs->ampurTarget);
		if (isset($rs->fundProblem)) $rs->fundProblem = floatval($rs->fundProblem);
		if (isset($rs->fundTarget)) $rs->fundTarget = floatval($rs->fundTarget);
		$result->summary[] = $rs;
	}

	return $result;
}
?>