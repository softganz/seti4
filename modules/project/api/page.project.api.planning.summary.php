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
function project_api_planning_summary($self) {
	$getDataType = SG\getFirst(post('dataType'),'json');
	$planSelect = SG\getFirst(post('plan'),NULL);
	$provinceSelect = SG\getFirst(post('chagwat'),NULL);
	$ampurSelect = SG\getFirst(post('ampur'), NULL);
	$areaSelect = SG\getFirst(post('area'),NULL);
	$sectorSelect = SG\getFirst(post('sector'),NULL);
	$yearSelect = post('year');
	$planIdSelect = post('pnid');

	$isDebug = user_access('access debugging program') && post('debug');
// debugMsg(post(),'post()');
	$filterArea = SG\getFirst(post('area'), is_array(post('for_area')) ? implode(',',post('for_area')) : NULL);
	$filterChangwat = SG\getFirst(post('changwat'), is_array(post('for_changwat')) ? implode(',',post('for_changwat')) : NULL);
	$filterAmpur = SG\getFirst(post('ampur'), is_array(post('for_ampur')) ? implode(',',post('for_ampur')) : NULL);
	$filterYear = SG\getFirst(post('year'), is_array(post('for_year')) ? implode(',',post('for_year')) : NULL);
	$filterSector = SG\getFirst(post('sector'), is_array(post('for_sector')) ? implode(',',post('for_sector')) : NULL);


	$result = (Object) [
		'status' => true,
		'count' => 0,
		'title' => 'สรุปแผนงาน',
		'debug' => $isDebug,
		'itemsFields' => [
			'plan' => 'แผนงาน',
			'amt' => 'จำนวนแผนงาน',
			'dev' => 'โครงการที่พัฒนา',
			'follow' => 'โครงการที่ติดตาม',
			'budget' => 'งบประมาณ',
		],
	];

	if ($isDebug) {
		$result->process = [];
		$urlQueryString = post();
		array_shift($urlQueryString);
		$result->process[] = 'URL = '._DOMAIN.$_SERVER['REQUEST_URI'].'?'.sg_implode_attr($urlQueryString,'&', '{quote: ""}');
	}




	// Count Project Planning On Issue
	//mydb::where('tr.`formid` = "info" AND tr.`part` = "title" AND p.`prtype`="แผนงาน"');
	mydb::where('p.`prtype`="แผนงาน" AND t.`orgid` IS NOT NULL');
	if ($filterArea) mydb::where('f.`areaid` IN ( :filterArea )', ':filterArea', 'SET:'.$filterArea);
	if ($filterAmpur) {
		mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET-STRING:'.$filterAmpur);
	} else if ($filterChangwat) {
		mydb::where('o.`changwat` IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
	}
	if ($filterSector) mydb::where('o.`sector` IN ( :filterSector )', ':filterSector', 'SET:'.$filterSector);
	if ($filterYear) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);


	/*
	$stmt = 'SELECT
		tg.`catid`
		, tg.`name` `label`
		, tg.`weight`
		, tg.`process`
		, tr.`tpid`, tr.`refid`
		, t.`orgid`
		, COUNT(*) `planamt`
		, COUNT(DISTINCT t.`orgid`) `orgamt`
		, CONCAT("project/planning/issue/",tg.`catid`) `url`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
			RIGHT JOIN %tag% tg ON tg.`taggroup` = "project:planning" AND tg.`catid` = tr.`refid`
		%WHERE%
		GROUP BY tg.`catid`
		ORDER BY `process` DESC,`catid` ASC;
		-- {sum:"planamt,orgamt"}
		';
	*/

	$stmt = 'SELECT
		tg.`catid`
		, tg.`name` `label`
		, tg.`weight`
		, tg.`process`
		, tr.`tpid`, tr.`refid`
		, t.`orgid`
		, COUNT(*) `planamt`
		, COUNT(DISTINCT t.`orgid`) `orgamt`
		, CONCAT("project/planning/issue/",tg.`catid`) `url`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
			LEFT JOIN %project_tr% tr ON tr.`tpid` = p.`tpid` AND tr.`formid` = "info" AND tr.`part` = "title"
			RIGHT JOIN %tag% tg ON tg.`taggroup` = "project:planning" AND tg.`catid` = tr.`refid`
		%WHERE%
		GROUP BY tg.`catid`
		ORDER BY tg.`process` DESC, tg.`weight`, CONVERT(tg.`name` USING tis620);
		-- PROJECT API PLANNING SUMMARY : COUNT PLANNING
		-- {sum:"planamt,orgamt"}
		';

	$planDbs = mydb::select($stmt);

	if ($isDebug) $result->process[] = mydb()->_query;
	//$ret .= print_o($planDbs,'$planDbs');





	// Count Project Proposal On Issue
	mydb::where('tg.`taggroup` = "project:planning"');
	if ($filterArea) mydb::where('f.`areaid` IN ( :filterArea )', ':filterArea', 'SET:'.$filterArea);
	if ($filterAmpur) {
		mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET:'.$filterAmpur);
	} else if ($filterChangwat) {
		mydb::where('o.`changwat` IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
	}
	if ($filterSector) mydb::where('o.`sector` IN ( :filterSector )', ':filterSector', 'SET:'.$filterSector);
	if ($filterYear) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);


	$stmt = 'SELECT
		tg.`catid`
		, tg.`name` `label`
		, tg.`weight`, tr.`tpid`, tr.`refid`
		, COUNT(*) `devamt`
		, COUNT(DISTINCT t.`orgid`) `orgamt`
		FROM %tag% tg
			LEFT JOIN %project_tr% tr
			ON tr.`formid`="develop" AND tr.`part`="supportplan" AND tr.`refid`=tg.`catid`
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project_dev% p USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
		%WHERE%
		GROUP BY tg.`catid`
		ORDER BY `process` DESC,`catid` ASC;
		-- PROJECT API PLANNING SUMMARY : COUNT PROPOSAL
		-- {key: "catid", sum:"devamt"}
	';

	$devDbs = mydb::select($stmt);

	if ($isDebug) $result->process[] = mydb()->_query;





	mydb::where('p.`prtype` = "โครงการ"');
	//mydb::where('tr.`formid` = "info" AND tr.`part` = "supportplan" AND p.`prtype` = "โครงการ"');
	if ($filterArea) mydb::where('f.`areaid` IN ( :filterArea )', ':filterArea', 'SET:'.$filterArea);
	if ($filterAmpur) {
		mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET:'.$filterAmpur);
	} else if ($filterChangwat) {
		mydb::where('o.`changwat` IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
	}
	if ($filterSector) mydb::where('o.`sector` IN ( :filterSector )', ':filterSector', 'SET:'.$filterSector);
	if ($filterYear) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);


	/*
	$stmt = 'SELECT
		tg.`catid`
		, tg.`name` `label`
		, tg.`weight`, tr.`tpid`, tr.`refid`
		, COUNT(*) `followAmt`
		, SUM(p.`budget`) `followBudget`
		, COUNT(DISTINCT t.`orgid`) `orgamt`
		FROM %project_tr% tr
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
			RIGHT JOIN %tag% tg ON tg.`taggroup` = "project:planning" AND tg.`catid` = tr.`refid`
		%WHERE%
		GROUP BY tg.`catid`
		ORDER BY `process` DESC,`catid` ASC;
		-- PROJECT API PLANNING SUMMARY
		-- {key: "catid", sum:"followAmt,followBudget"}
	';
	*/

	// Count Project Follow On Issue
	$stmt = 'SELECT
		tg.`catid`
		, tg.`name` `label`
		, tg.`weight`, tr.`tpid`, tr.`refid`
		, COUNT(*) `followAmt`
		, SUM(p.`budget`) `followBudget`
		, COUNT(DISTINCT t.`orgid`) `orgamt`
		FROM
		(SELECT tr.`tpid`, tr.`refid`
			FROM %project_tr% tr
			WHERE tr.`formid` = "info" AND tr.`part` = "supportplan"
		) tr
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`
			RIGHT JOIN %tag% tg ON tg.`taggroup` = "project:planning" AND tg.`catid` = tr.`refid`
		%WHERE%
		GROUP BY tg.`catid`
		ORDER BY `process` DESC,`catid` ASC;
		-- PROJECT API PLANNING SUMMARY : COUNT FOLLOW
		-- {key: "catid", sum:"followAmt,followBudget"}
	';

	$projectDbs = mydb::select($stmt);

	if ($isDebug) $result->process[] = mydb()->_query;



	$labelText='แผนงาน';

	$totalDev = $totalFollow = $totalBudget = 0;

	$tables = new Table();
	$tables->thead = array(
		'title' => $labelText,
		'amt -org' => 'จำนวนแผนงาน',
		'amt -dev' => 'โครงการที่พัฒนา',
		'amt -project' => 'โครงการที่ติดตาม',
		'budget -money' => 'งบประมาณ(บาท)'
	);

	$parameter = ['area' => $filterArea, 'changwat' => $filterChangwat, 'ampur' => $filterAmpur, 'sector' => $filterSector, 'year' => $filterYear];

	foreach ($planDbs->items as $planRs) {
		$devRs = $devDbs->items[$planRs->catid];
		$projectRs = $projectDbs->items[$planRs->catid];

		$tables->rows[] = array(
			'<a class="sg-action" href="'.url($planRs->url, $parameter).'" data-rel="box" data-width="full" title="จำนวน '.$planRs->planamt.' แผนงาน '.$planRs->orgamt.' กองทุน">'.$planRs->label.'</a>',
			'<a class="sg-action" href="'.url($planRs->url, $parameter).'" data-rel="box" data-width="full" title="จำนวน '.$planRs->planamt.' แผนงาน '.$planRs->orgamt.' กองทุน">'.$planRs->planamt.'</a>',
			'<a class="sg-action" href="'.url('project/planning/dev/'.$devRs->catid, $parameter).'" data-rel="box" data-width="full">'.$devRs->devamt.'</a>',
			'<a class="sg-action" href="'.url('project/planning/follow/'.$projectRs->catid, $parameter).'" data-rel="box" data-width="full">'.$projectRs->followAmt.'</a>',
			number_format($projectRs->followBudget,2),
			//.'('.$devDbs->items[$planRs->catid]->orgamt.')',
			//'config' => array('class' => $planRs->orgamt != $planRs->devamt ? '-error' : ''),
		);

		$totalDev += $devRs->devamt;
		$totalFollow += $projectRs->followAmt;
		$totalBudget += $projectRs->followBudget;
	}
	$tables->tfoot[] = array(
		'',
		number_format($planDbs->sum->planamt),
		number_format($totalDev),
		number_format($totalFollow),
		number_format($totalBudget,2),
	);

	foreach ($tables->rows as $key => $row) {
		$result->items[] = array(
			'plan' => $row[0],
			'amt' => $row[1],
			'dev' => $row[2],
			'follow' => $row[3],
			'budget' => $row[4],
			'config' => '{}',
		);
	}

	$result->html = $tables->build();


	// debugMsg(mydb()->_query); $result->query = mydb()->_query;

	$result->count = $projectDbs->_num_rows;
	$result->query = mydb()->_query;
	//debugMsg($result,'$result');
	//return sg_json_encode($result);
	return $getDataType == 'json' ? $result : $item->html;
}
?>