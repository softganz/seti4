<?php
/**
* Green API :: Plannt Summary
* Created 2021-01-17
* Modify  2021-01-17
*
* @param Object $self
* @param Array $REQUEST
* @return JSON
*/
function green_api_plant($self) {
	$getDataType = SG\getFirst(post('dataType'),'json');
	$planSelect = SG\getFirst(post('pn'),NULL);
	$provinceSelect = SG\getFirst(post('pv'),NULL);
	$ampurSelect = SG\getFirst(post('am'), NULL);
	$areaSelect = SG\getFirst(post('ar'),NULL);
	$sectorSelect = SG\getFirst(post('s'),NULL);
	$yearSelect = post('yr');
	$planIdSelect = post('pnid');

	$isDebug = user_access('access debugging program') && post('debug');

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
		'orgName' => 'กลุ่ม/เครือข่าย',
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



	mydb::where('p.`tagname` = "GREEN,PLANT"');
	if ($filterAmpur) {
		mydb::where('LEFT(o.`areacode`,4`) IN ( :filterAmpur )', ':filterAmpur', 'SET-STRING:'.$filterAmpur);
	} else if ($filterChangwat) {
		mydb::where('LEFT(o.`areacode`,2) IN ( :filterChangwat )', ':filterChangwat', 'SET:'.$filterChangwat);
	}
	//if ($filterSector) mydb::where('o.`sector` IN ( :filterSector )', ':filterSector', 'SET:'.$filterSector);
	//if ($filterYear) mydb::where('p.`pryear` IN ( :filterYear )', ':filterYear', 'SET:'.$filterYear);

	$stmt = 'SELECT
		p.`orgid`
		, p.`productname`
		, p.`startdate`
		, p.`cropdate`
		, SUM(p.`qty`) `qty`
		, o.`name` `orgName`
		FROM %ibuy_farmplant% p
			LEFT JOIN %db_org% o ON o.`orgid` = p.`orgid`
		%WHERE%
		GROUP BY p.`orgid`,p.`productname`
		ORDER BY CONVERT(`productname` USING tis620);
		-- PROJECT API PLANNING SUMMARY : COUNT PLANNING
		-- {sum:"planamt,orgamt"}
		';

	$planDbs = mydb::select($stmt);

	if ($isDebug) $result->process[] = mydb()->_query;
	//debugMsg($planDbs,'$planDbs');
	//return;




	$labelText='แผนงาน';

	$totalDev = $totalFollow = $totalBudget = 0;

	$tables = new Table();
	$tables->thead = array(
		'orgName' => 'กลุ่ม',
		//'start -date' => 'วันที่ปลูก',
		'crop -date' => 'วันที่เก็บเกี่ยว',
		'qty -amt' => 'ปริมาณ',
	);

	foreach ($planDbs->items as $planRs) {
		$tables->rows[] = array(
			//'<a class="sg-action" href="'.url('green/report/plant', array('org' => $planRs->orgid)).'" data-rel="box" title="">'.$planRs->productname.'<br />'.$planRs->orgName.'</a>',
			$planRs->productname.'<br /><em>'.$planRs->orgName.'</em>',
			//$planRs->startdate ? sg_date($planRs->startdate,'ว ดด ปปปป') : '',
			$planRs->cropdate ? sg_date($planRs->cropdate,'ว ดด ปปปป') : '',
			$planRs->qty,
		);

		$totalDev += $devRs->devamt;
		$totalFollow += $projectRs->followAmt;
		$totalBudget += $projectRs->followBudget;
	}
	/*
	$tables->tfoot[] = array(
		'',
		number_format($planDbs->sum->planamt),
		number_format($totalDev),
		number_format($totalFollow),
	);
	*/

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


	//debugMsg(mydb()->_query); $result->query = mydb()->_query;

	$result->count = $dbs->_num_rows;
	$result->query = mydb()->_query;
	//debugMsg($result,'$result');
	//return sg_json_encode($result);
	return $getDataType == 'json' ? $result : $item->html;
}
?>