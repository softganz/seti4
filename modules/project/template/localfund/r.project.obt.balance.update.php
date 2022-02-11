<?php
/**
* OBT Calculate Fund Balance
* Created 2019-05-24
* Modify  2019-05-24
*
* @param Int $fundInfo
* @return Array
*/

$debug = true;

function r_project_obt_balance_update($fundInfo, $options = '{}') {
	$defaults = '{debug: false, update: true}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;


	if (!$fundInfo) return NULL;

	$result = new StdClass();

	$result->orgid = $fundInfo->orgid;
	$result->fundid = $fundInfo->fundid;
	$result->fundname = $fundInfo->name;
	$result->areaid = $fundInfo->info->areaid;
	$result->opendate = $fundInfo->info->openbaldate;
	$result->openbalance = $fundInfo->info->openbalance;

	$result->revenue = 0;
	$result->expenditure = 0;
	$result->balance = 0;
	$result->count = 0;

	$result->trans = Array();

	if ($debug) $result->query = Array();


	mydb::where('o.`areaid` = 12 AND gc.`gltype` IN (4,5) AND g.`refdate` >= o.`openbaldate`');
	mydb::where('g.`orgid` = :orgid',':orgid', $fundInfo->orgid);


	$stmt = 'SELECT
					  b.`REPORT_ID`
					, a.*
					FROM (SELECT
					  o.`fundid` `FUND_CODE`
					, YEAR(g.`refdate`) + IF(MONTH(g.`refdate`) >= 10, 1, 0) `BUDGET_YEAR`
					, MONTH(g.`refdate`) `MONTH_ID`
					, DATE_FORMAT(g.`refdate`,"%Y-%m") `MONTH_REF`
					, ABS(SUM(IF(gc.`gltype` = 4, g.`amount`, 0))) `REVENUE`
					, ABS(SUM(IF(gc.`gltype` = 5, g.`amount`, 0))) `EXPENDITURE`
					FROM %project_gl% g
						LEFT JOIN %project_fund% o USING(`orgid`)
						LEFT JOIN %glcode% gc USING(`glcode`)
				%WHERE%
				GROUP BY `MONTH_REF`
				ORDER BY `MONTH_REF` ASC) a
					LEFT JOIN %obt62_balance_report% b
						ON b.`FUND_CODE` = a.`FUND_CODE` AND b.`BUDGET_YEAR` = a.`BUDGET_YEAR` AND b.`MONTH_ID` = a.`MONTH_ID`;
				-- {key:"MONTH_REF",sum:"REVENUE,EXPENDITURE"}';

	$dbs = mydb::select($stmt,$where['value']);

	if ($debug) $result->query[] = mydb()->_query;

	// Insert empty month from openbalancedate to now

	$startDate = $result->opendate;
	$endDate = date('Y-m-d');

	while (strtotime($startDate) <= strtotime($endDate)) {
		$month = date('Y-m', strtotime($startDate));
		$key = $month;
		if (!isset($dbs->items[$key])) {
			$dbs->items[$key] = (object) array(
														'REPORT_ID' => NULL,
														'FUND_CODE' => $result->fundid,
														'BUDGET_YEAR' => intval(sg_date($month.'-01', 'Y')) + (sg_date($month.'-01', 'm') >= 10 ? 1 : 0),
														'MONTH_ID' => sg_date($month.'-01', 'm'),
														'MONTH_REF' => $month,
														'REVENUE' => 0,
														'EXPENDITURE' => 0,
													);
		}
		$startDate = date('d M Y', strtotime($startDate.
		'+ 1 month'));
	}

	ksort($dbs->items);

	$balance = $result->openbalance;
	foreach ($dbs->items as $key => $rs) {
		$balance = $balance + $rs->REVENUE - $rs->EXPENDITURE;
		$rs->BALANCE_FORWARD = $balance;
		//$dbs->items[$key] = $rs;

		if ($options->update) {
			$stmt = 'INSERT INTO %obt62_balance_report%
							(`REPORT_ID`, `FUND_CODE`, `BUDGET_YEAR`, `MONTH_ID`, `BALANCE_FORWARD`, `REVENUE`, `EXPENDITURE`)
							VALUES
							(:REPORT_ID, :FUND_CODE, :BUDGET_YEAR, :MONTH_ID, :BALANCE_FORWARD, :REVENUE, :EXPENDITURE)
							ON DUPLICATE KEY UPDATE
							`BALANCE_FORWARD` = :BALANCE_FORWARD
							, `REVENUE` = :REVENUE
							, `EXPENDITURE` = :EXPENDITURE
							';
			mydb::query($stmt, $rs);
			if ($debug) $result->query[] = mydb()->_query;
		}
	}


	$result->balance = $balance;
	$result->count = $dbs->_num_rows;
	$result->revenue = $dbs->sum->REVENUE;
	$result->expenditure = $dbs->sum->EXPENDITURE;

	$result->trans = $dbs->items;

	//debugMsg($dbs,'$dbs');
	//debugMsg($fundInfo,'$fundInfo');
	return $result;
}
?>