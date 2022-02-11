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
function project_api_valuations_summary($self, $param = NULL) {
	$filterValuation = SG\getFirst($param->totalValuation, post('value'), implode(',',post('for_value')));
	$filterPlan = SG\getFirst($param->filterPlan, post('plan'), implode(',',post('for_plan')));
	$filterArea = SG\getFirst($param->filterArea, post('area'),implode(',',post('for_area')));
	$filterChangwat = SG\getFirst($param->filterChangwat, post('changwat'),implode(',',post('for_changwat')));
	$filterAmpur = SG\getFirst($param->filterAmpur, post('ampur'),implode(',',post('for_ampur')));
	$filterYear = SG\getFirst($param->filterYear, post('year'),implode(',',post('for_year')));
	$filterSector = SG\getFirst($param->filterSector, post('s'),implode(',',post('for_sector')));
	$filterFund = SG\getFirst($param->filterFund, post('fund'),implode(',',post('for_fund')));
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
	if ($filterFund) $result->parameter->fund = $filterFund;
	if ($filterType) $result->parameter->type = $filterType;

	$result->summaryFields = [
		'label' => 'ปี พ.ศ.',
		'totalProject' => 'โครงการทั้งหมด',
		'totalValuation' => 'โครงการเกิดคุณค่า',
	];
	if ($filterArea) $result->summaryFields['totalArea'] = 'เขต '.$filterArea;
	if ($filterChangwat) {
		$result->summaryFields['totalChangwat'] = 'จังหวัด'
			. mydb::select('SELECT * FROM %co_province% WHERE `provid` = :changwat LIMIT 1', ':changwat', $filterChangwat)->provname;
	}
	if ($filterAmpur) {
		$result->summaryFields['totalAmpur'] = 'อำเภอ'
			. mydb::select('SELECT * FROM %co_district% WHERE `distid` = :ampur LIMIT 1', ':ampur', $filterAmpur)->distname;
	}
	if ($filterFund) {
		$result->summaryFields['totalFund'] = mydb::select('SELECT * FROM %db_org% WHERE `orgid` = :orgid LIMIT 1', ':orgid', $filterFund)->name;
	}
	//if ($filterValuation) $result->summaryFields['totalByValuation'] = 'คุณค่า '.$filterValuation;


	$result->summary = array();

	if ($isDebug && !$param) {
		$result->process = array();
		$urlQueryString = post();
		array_shift($urlQueryString);
		$result->process[] = 'URL = '._DOMAIN.$_SERVER['REQUEST_URI'].'?'.sg_implode_attr($urlQueryString,'&', '{quote: ""}');
		$result->process[] = print_o(post(),'post()');
		$result->process[] = print_o($result->parameter, 'parameter');
	}


	$totalList = [];
	$join = [];
	$valuationCondition = $filterValuation ? ' AND v.`part` LIKE :part' : '';

	mydb::where('p.`project_status` IN ("ดำเนินการเสร็จสิ้น")');
	if ($getYearStart) mydb::where('p.`pryear` >= :yearStart', ':yearStart', $getYearStart);
	if ($filterValuation) {
		mydb::where(NULL, ':part', $filterValuation.'%');
	}
	//mydb::where('v.`rate1` = 1');
	//if ($getChangwat) mydb::where('LEFT(t.`areacode`,2) = :changwat',':changwat',$getChangwat);
	//if ($getInno) mydb::where('v.`part` LIKE :part',':part',$getInno.'%');
	$totalList[] = 'COUNT(DISTINCT IF(v.`rate1` = 1 '.$valuationCondition.', p.`tpid`, NULL)) `totalValuation`';
	if ($filterArea) {
		$totalList[] = 'COUNT(DISTINCT IF(f.`areaid` = :areaId'.$valuationCondition.' AND v.`rate1` = 1, p.`tpid`, NULL)) `totalArea`';
		$totalList[] = 'COUNT(DISTINCT IF(f.`areaid` = :areaId, p.`tpid`, NULL)) `totalAreaFollow`';
		mydb::where(NULL,':areaId', $filterArea);
		$join[] = 'LEFT JOIN %project_fund% f ON f.`orgid` = o.`orgid`';
	}
	if ($filterChangwat) {
		$totalList[] = 'COUNT(DISTINCT IF(t.`areacode` LIKE :changwat'.$valuationCondition.' AND v.`rate1` = 1, p.`tpid`, NULL)) `totalChangwat`';
		$totalList[] = 'COUNT(DISTINCT IF(t.`areacode` LIKE :changwat, p.`tpid`, NULL)) `totalChangwatFollow`';
		mydb::where(NULL,':changwat', $filterChangwat.'%');
	}
	if ($filterAmpur) {
		$totalList[] = 'COUNT(DISTINCT IF(t.`areacode` LIKE :ampur'.$valuationCondition.' AND v.`rate1` = 1, p.`tpid`, NULL)) `totalAmpur`';
		$totalList[] = 'COUNT(DISTINCT IF(t.`areacode` LIKE :ampur, p.`tpid`, NULL)) `totalAmpurFollow`';
		mydb::where(NULL,':ampur', $filterAmpur.'%');
	}
	if ($filterFund) {
		$totalList[] = 'COUNT(DISTINCT IF(t.`orgid` = :fund'.$valuationCondition.' AND v.`rate1` = 1, p.`tpid`, NULL)) `totalFund`';
		$totalList[] = 'COUNT(DISTINCT IF(t.`orgid` = :fund, p.`tpid`, NULL)) `totalFundFollow`';
		mydb::where(NULL,':fund', $filterFund);
	}

	if ($filterYear) {
		mydb::where('p.`pryear` = :projectYear', ':projectYear', $filterYear);
		//$totalList[] = 'COUNT(DISTINCT IF(v.`part` LIKE :part AND v.`rate1` = 1, p.`tpid`, NULL)) `totalByValuation`';
	}
	if ($filterValuation) {
		//mydb::where('v.`part` LIKE :part', ':part', $filterValuation.'%');
		//$totalList[] = 'COUNT(DISTINCT IF(v.`part` LIKE :part AND v.`rate1` = 1, p.`tpid`, NULL)) `totalByValuation`';
	} else {
		//$totalList[] = 'COUNT(DISTINCT IF(v.`rate1` = 1, p.`tpid`, NULL)) `totalRate`';
	}

	if ($filterType) {
		mydb::where('p.`supporttype` IN ( :supportType )', ':supportType', 'SET:'.$filterType);
	}

	mydb::value('$FIELD$', ($totalList ? ', ' : '').implode(_NL.'		, ', $totalList), false);
	mydb::value('$JOIN$', implode(_NL, $join));

	$stmt = 'SELECT
		p.`pryear` `year`
		, COUNT(DISTINCT p.`tpid`) `totalProject`
		$FIELD$
		-- , p.`tpid`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			$JOIN$
			LEFT JOIN %project_tr% v ON v.`tpid` = p.`tpid` AND v.`formid` IN ("valuation","ประเมิน")
		%WHERE%
		GROUP BY p.`pryear`
		ORDER BY p.`pryear` ASC';

	$dbs = mydb::select($stmt);

	if ($isDebug) $result->process[] = '<pre>'.mydb()->_query.'</pre>';

	$result->count = $dbs->count();

	foreach ($dbs->items as $rs) {
		$result->summary[] = $rs;
		/*
		$data = (Object) [
			'year' => $rs->pryear,
			'totalProject' => intval($rs->totalProject),
			'totalValuation' => intval($rs->totalValuation),
		];
		if ($filterArea) $data->totalArea = intval($rs->totalArea);
		if ($filterChangwat) $data->totalChangwat = intval($rs->totalChangwat);
		if ($filterAmpur) $data->totalAmpur = intval($rs->totalAmpur);
		if ($filterFund) {
			$data->totalFund = intval($rs->totalFund);
			$data->totalFundFollow = intval($rs->totalFundFollow);
		}
		//if ($filterValuation) $data->totalByValuation = intval($rs->totalByValuation);
		$result->summary[] = $data;
		*/
	}

	return $result;
}
?>