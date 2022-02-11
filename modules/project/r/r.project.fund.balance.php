<?php
/**
* Model Name
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_project_fund_balance($fundInfo, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$result->orgId = $fundInfo->orgid;
	$result->fundId = $fundInfo->fundid;
	$result->fundName = $fundInfo->name;
	$result->areaId = $fundInfo->info->areaid;
	$result->openBalance = round($fundInfo->info->openbalance,2);
	$result->closeBalance = 0;
	$result->totalRevenue = 0;
	$result->totalExpend = 0;

	mydb::where('gc.`gltype` IN (4,5) AND gl.`refdate` >= :openbaldate', ':openbaldate' , $fundInfo->info->openbaldate);
	mydb::where('gl.`orgid` = :orgid',':orgid',$fundInfo->orgid);

	$stmt = 'SELECT
		  gl.`orgid`
		, DATE_FORMAT(gl.`refdate`,"%Y-%m") `refmonth`
		, YEAR(gl.`refdate`) + IF(MONTH(gl.`refdate`) >= 10,1,0) `budgetYear`
		, LPAD(MONTH(gl.`refdate`),2,"0") `budgetMonth`
		, CASE
			WHEN MONTH(gl.`refdate`) >= 10 THEN 1
			WHEN MONTH(gl.`refdate`) >= 7 THEN 4
			WHEN MONTH(gl.`refdate`) >= 4 THEN 3
			WHEN MONTH(gl.`refdate`) >= 1 THEN 2
		END `budgetQuarter`
		, ABS(SUM(IF(gc.`gltype` = 4,gl.`amount`,0))) `revenue`
		, ABS(SUM(IF(gc.`gltype` = 5,gl.`amount`,0))) `expend`
		FROM %glcode% gc
			LEFT JOIN %project_gl% gl USING(`glcode`)
		%WHERE%
		GROUP BY `refmonth`
		ORDER BY `refmonth` ASC;
		-- {key:"refmonth",sum:"revenue,expend"}';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//debugMsg($dbs,'$dbs');

	$result->totalRevenue = round($dbs->sum->revenue,2);
	$result->totalExpend = round($dbs->sum->expend,2);

	$result->balance = $dbs->items;

	//123 = 1 456 = 2 789 = 3
	// Insert empty month from openbalancedate to now
	$startDate = $fundInfo->info->openbaldate;
	$endDate = date('Y-m-d');

	while (strtotime($startDate) <= strtotime($endDate)) {
		$month = sg_date($startDate, 'Y-m');
		//debugMsg('$startDate = '.$startDate.' $endDate = '.$endDate.' $month = '.$month);
		if (!isset($result->balance[$month])) {
			$result->balance[$month] = (Object) Array(
				'orgid' => $fundInfo->orgid,
				'refmonth' => sg_date($startDate, 'Y-m'),
				'budgetYear' => sg_budget_year($startDate),
				'budgetMonth' => sg_date($startDate,'m'),
				'budgetQuarter' => sg_date($startDate, 'm') >= 10 ? 1 : floor((sg_date($startDate, 'm') - 1)/3) + 2,
				'revenue' => 0.00,
				'expend' => 0.00,
			);
			//debugMsg('$startDate = '.$startDate.' '.'DATE = '.sg_date($startDate,'Y-m-d'));
		}
		$startDate = date('Y-m-d', strtotime($startDate.'+ 1 month'));
	}

	ksort($result->balance);

	$balance = $result->openBalance;

	foreach ($result->balance as $key => $value) {
		$value->openBalance = $balance;
		$value->revenue = round($value->revenue,2);
		$value->expend = round($value->expend,2);
		$monthBalance = round($value->revenue - $value->expend,2);
		$balance += $monthBalance;
		$balance = round($balance,2);
		$value->closeBalance = $balance;
	}

	$result->closeBalance = $balance;

	return $result;
}
?>