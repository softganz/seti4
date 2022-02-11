<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_nhso_api_project($self) {
	$getTarget = SG\getFirst(post('target'),'1001');
	$getAct = SG\getFirst(post('act'),'29');
	$getYear = intval(SG\getFirst(post('year'),date('m')>=10 ? date('Y') + 1 : date('Y')));
	$getZone = SG\getFirst(post('zone'),12);
	$getProvince = post('prov');
	$getDist = post('dist');
	$getFund = post('fund');

	sendHeader('application/json');


	//header('Access-Control-Allow-Origin: https://editor.swagger.io');
	header('Access-Control-Allow-Origin: *');
	$headerResult = http_response_code(200);

	// Check multiple origin
	$http_origin = $_SERVER['HTTP_ORIGIN'];
	if (in_array($http_origin, array("http://www.domain1.com","http://www.domain2.com"))) {
		header("Access-Control-Allow-Origin: $http_origin");
	}

	header('SG-Access-Origin: '.$http_origin);

	$ret = '';

	//$ret .= 'HTTP_ORIGIN = '.$http_origin;

	//$ret .= $_SERVER['SERVER_PROTOCOL'];
	//$ret .= $headerResult;

	mydb::where('tp.`parent` = :parent AND tp.`tgtid` = :tgtid', ':parent', $getAct, ':tgtid', $getTarget);
	mydb::where('p.`pryear` = :year', ':year', $getYear);
	mydb::where('o.`sector` = 9');

	mydb::value('$GROUPBY$', ' ');
	if ($getFund) {
		mydb::value('$FIELD_AREACODE$',', LEFT(o.`areacode`,2) `areacode`');
		mydb::where('f.`fundid` = :fundid', ':fundid', $getFund);
		mydb::value('$GROUPBY$', 'GROUP BY `areacode`');
		mydb::value('$TOTALORG$', ', COUNT(*) `totalFund`');
		mydb::value('$TOTALPROJECT$', ', SUM(`orgProject`) `totalProject`');
	} else if ($getDist) {
		mydb::value('$FIELD_AREACODE$',', LEFT(o.`areacode`,4) `areacode`');
		mydb::where('LEFT(o.`areacode`,4) = :areacode', ':areacode', $getDist);
		mydb::value('$GROUPBY$', 'GROUP BY `areacode`');
		mydb::value('$TOTALORG$', ', COUNT(*) `totalFund`');
		mydb::value('$TOTALPROJECT$', ', SUM(`orgProject`) `totalProject`');
	} else if ($getProvince) {
		mydb::value('$FIELD_AREACODE$',', LEFT(o.`areacode`,2) `areacode`');
		mydb::where('LEFT(o.`areacode`,2) = :areacode', ':areacode', $getProvince);
		mydb::value('$GROUPBY$', 'GROUP BY `areacode`');
		mydb::value('$TOTALORG$', ', COUNT(*) `totalFund`');
		mydb::value('$TOTALPROJECT$', ', SUM(`orgProject`) `totalProject`');
	} else if ($getZone) {
		mydb::where('f.`areaid` = :areaid', ':areaid', $getZone);
		mydb::value('$FIELD_AREACODE$',', NULL `areacode`');
		mydb::value('$GROUPBY$', 'GROUP BY `areacode`');
		mydb::value('$TOTALORG$', ', COUNT(*) `totalFund`');
		mydb::value('$TOTALPROJECT$', ', SUM(`orgProject`) `totalProject`');
	}

	$stmt = 'SELECT
		a.*
		$TOTALORG$
		$TOTALPROJECT$
		, ad.`areaname`
		, cop.`provname` `changwatName`
		, cod.`distname` `ampurName`
		FROM
		(SELECT
		tp.`tpid`, tp.`parent`,tp.`tgtid`,p.`pryear`
		, t.`orgid`
		, o.`name` `orgName`
		, f.`areaid`
		, f.`fundid`
		$FIELD_AREACODE$
		, COUNT(`tpid`) `orgProject`
		FROM %topic_parent% tp
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_fund% f USING(`orgid`)
		%WHERE%
		GROUP BY `orgid`
		) a
			LEFT JOIN %project_area% ad ON ad.`areatype` = "nhso" AND ad.`areaid` = a.`areaid`
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(`areacode`,2)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(`areacode`,4)
		$GROUPBY$
		LIMIT 1
		';

	$rs = mydb::select($stmt);

	if ($getFund) {
		$result = [
			'fundcode' => $rs->fundid.'',
			'fundname' => $rs->orgName,
			'year' => $getYear,
			'fund' => intval($rs->totalFund),
			'project' => intval($rs->totalProject),
		];
	} else if ($getDist) {
		$result = [
			'districtcode' => $rs->areacode.'',
			'districtame' => $rs->ampurName,
			'year' => $getYear,
			'fund' => intval($rs->totalFund),
			'project' => intval($rs->totalProject),
		];
	} else if ($getProvince) {
		$result = [
			'provincecode' => $rs->areacode.'',
			'provincename' => $rs->changwatName,
			'year' => $getYear,
			'fund' => intval($rs->totalFund),
			'project' => intval($rs->totalProject),
		];
	} else if ($getZone) {
		$result = [
			'zonecode' => $rs->areaid.'',
			'zonename' => $rs->areaname,
			'year' => $getYear,
			'fund' => intval($rs->totalFund),
			'project' => intval($rs->totalProject),
		];
	}
	$ret = SG\json_encode($result);

	//$ret .= print_o($rs,'$rs');

	// print $ret;
	// die;
	// die($ret);
	return $result;
}
?>