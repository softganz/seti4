<?php
/**
* Project API :: Planning Situation
* Created 2021-04-11
* Modify  2021-04-11
*
* @param Object $self
* @param Object $param
* @param Array $REQUEST
* @return JSON
*/
function project_api_planning_situations($self, $param = NULL) {
	$filterValuation = SG\getFirst($param->filterValuation, post('value'), implode(',',post('for_value')));
	$filterPlan = SG\getFirst($param->filterPlan, post('plan'), implode(',',post('for_plan')));
	$filterArea = SG\getFirst($param->filterArea, post('area'),implode(',',post('for_area')));
	$filterChangwat = SG\getFirst($param->filterChangwat, post('changwat'),implode(',',post('for_changwat')));
	$filterAmpur = SG\getFirst($param->filterAmpur, post('ampur'),implode(',',post('for_ampur')));
	$filterYear = SG\getFirst($param->filterYear, post('year'),implode(',',post('for_year')));
	$filterSector = SG\getFirst($param->filterSector, post('s'),implode(',',post('for_sector')));
	$filterOrg = SG\getFirst($param->filterOrg, post('org'),implode(',',post('for_org')));
	$filterType = SG\getFirst($param->filterType, post('type'),implode(',',post('for_type')));

	$getYearStart = post('yearStart');

	$isDebug = user_access('access debugging program') && post('debug');

	header('Access-Control-Allow-Origin: *');
	//$headerResult = http_response_code(200);

	$followCfg = cfg('project')->follow;


	$result->status = true;
	$result->count = 0;
	$result->debug = $isDebug;
	$result->parameter = (Object) [];

	if ($filterValuation) $result->parameter->value = $filterValuation;
	if ($filterPlan) $result->parameter->plan = $filterPlan;
	if ($filterArea) $result->parameter->area = $filterArea;
	if ($filterChangwat) $result->parameter->changwat = $filterChangwat;
	if ($filterAmpur) $result->parameter->ampur = $filterAmpur;
	if ($filterYear) $result->parameter->year = $filterYear;
	if ($filterOrg) $result->parameter->org = $filterOrg;
	if ($filterType) $result->parameter->type = $filterType;

	if ($isDebug && !$param) {
		$result->process = array();
		$urlQueryString = post();
		array_shift($urlQueryString);
		$result->process[] = 'URL = '._DOMAIN.$_SERVER['REQUEST_URI'].'?'.sg_implode_attr($urlQueryString,'&', '{quote: ""}');
		$result->process[] = print_o(post(),'post()');
	}


	$totalList = [];
	$join = [];
	mydb::where('p.`project_status` IN ("ดำเนินการเสร็จสิ้น") AND v.`rate1` = 1');
	if ($getYearStart) mydb::where('p.`pryear` >= :yearStart', ':yearStart', $getYearStart);
	//mydb::where('v.`rate1` = 1');
	//if ($getChangwat) mydb::where('LEFT(t.`areacode`,2) = :changwat',':changwat',$getChangwat);
	//if ($getInno) mydb::where('v.`part` LIKE :part',':part',$getInno.'%');
	if ($filterArea) {
		mydb::where('f.`areaid` = :areaId',':areaId', $filterArea);
		$join[] = 'LEFT JOIN %project_fund% f ON f.`orgid` = o.`orgid`';
	}
	if ($filterChangwat) {
		mydb::where('t.`areacode` LIKE :changwat',':changwat', $filterChangwat.'%');
	}
	if ($filterAmpur) {
		$totalList[] = 'COUNT(DISTINCT IF(t.`areacode` LIKE :ampur  '.($filterValuation ? 'AND v.`part` LIKE :part' : '').' AND v.`rate1` = 1, p.`tpid`, NULL)) `totalAmpur`';
		mydb::where('t.`areacode` LIKE :ampur',':ampur', $filterAmpur.'%');
	}
	if ($filterOrg) {
		$totalList[] = 'COUNT(DISTINCT IF(t.`orgid` = :fund  '.($filterValuation ? 'AND v.`part` LIKE :part' : '').' AND v.`rate1` = 1, p.`tpid`, NULL)) `totalFund`';
		mydb::where('t.`orgid` = :fund',':fund', $filterOrg);
	}

	if ($filterYear) {
		mydb::where('p.`pryear` = :projectYear', ':projectYear', $filterYear);
	}
	if ($filterValuation) {
		mydb::where('v.`part` LIKE :part', ':part', $filterValuation.'%');
	}
	if ($filterType) {
		mydb::where('p.`supporttype` IN ( :supportType )', ':supportType', 'SET:'.$filterType);
	}

	mydb::value('$FIELD$', ($totalList ? ', ' : '').implode(_NL.'		, ', $totalList), false);
	mydb::value('$JOIN$', implode(_NL, $join));

	$stmt = 'SELECT
		p.`tpid` `projectId`
		, t.`title`
		, o.`name` `orgName`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			$JOIN$
			LEFT JOIN %project_tr% v ON v.`tpid` = p.`tpid` AND v.`formid` IN ("valuation","ประเมิน")
		%WHERE%
		GROUP BY `projectId`
		ORDER BY CONVERT(`title` USING tis620) ASC';

	$dbs = mydb::select($stmt);

	if ($isDebug) $result->process[] = '<pre>'.mydb()->_query.'</pre>';

	$result->count = $dbs->count();

	foreach ($dbs->items as $rs) {
		$result->items[] = $rs;
	}

	return $result;
}
?>