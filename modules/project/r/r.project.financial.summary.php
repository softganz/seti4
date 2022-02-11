<?php
/**
* Get Finance Summary Information
* Created 2017-10-09
* Modify  2020-04-28
*
* @param Object $fundInfo
* @return String
*/

$debug = true;

function r_project_financial_summary($fundInfo) {
	$orgId = $fundInfo->orgid;
	$currentBudgetYear = sg_budget_year(date('Y-m-d'));
	$openBalanceAmount = $fundInfo->info->openbalance;
	$openBalanceDate = $fundInfo->info->openbaldate;

	$ret = '';

	$stmt = 'SELECT
		  LEFT(`glcode`,1) `glGroup`
		, ABS(SUM(`amount`)) `amount`
		FROM %project_gl%
		WHERE `orgid` = :orgid AND `refdate` >= :openBalanceDate AND YEAR(`refdate`)+IF(QUARTER(`refdate`) = 4,1,0) = :year
		GROUP BY `glGroup`;
		-- {key:"glGroup"}';

	$thisYearSum = mydb::select($stmt,':orgid',$orgId, ':openBalanceDate', $openBalanceDate, ':year',$currentBudgetYear)->items;

	$stmt = 'SELECT
		  LEFT(`glcode`,1) `glGroup`
		, ABS(SUM(`amount`)) `amount`
		FROM %project_gl%
		WHERE `orgid` = :orgid AND `refdate` >= :openBalanceDate AND YEAR(`refdate`)+IF(QUARTER(`refdate`) = 4,1,0) = :year - 1
		GROUP BY `glGroup`;
		-- {key:"glGroup"}';

	$prevYearSum=mydb::select($stmt,':orgid',$orgId, ':openBalanceDate', $openBalanceDate, ':year',$currentBudgetYear)->items;

	$stmt = 'SELECT
		  LEFT(`glcode`,1) `glGroup`
		, ABS(SUM(`amount`)) `amount`
		FROM %project_gl%
		WHERE `orgid` = :orgid AND `refdate` >= :openBalanceDate
		GROUP BY `glGroup`;
		-- {key:"glGroup"}';

	$allYearSum = mydb::select($stmt,':orgid',$orgId, ':openBalanceDate', $openBalanceDate)->items;




	$lastYearOpenBalance = $openBalanceAmount
		+ ($allYearSum[4]->amount - $allYearSum[5]->amount)
		- ($prevYearSum[4]->amount - $prevYearSum[5]->amount)
		- ($thisYearSum[4]->amount - $thisYearSum[5]->amount);

	$lastYearCloseBalance = $lastYearOpenBalance + $prevYearSum[4]->amount - $prevYearSum[5]->amount;

	$currentDateBalance = $openBalanceAmount + ($allYearSum[4]->amount - $allYearSum[5]->amount);

	$currentYearOpenBalance = $lastYearCloseBalance;

	$currentYearBalance = $lastYearCloseBalance + ($thisYearSum[4]->amount - $thisYearSum[5]->amount);

	//debugMsg($thisYearSum,'$thisYearSum');
	//debugMsg($prevYearSum,'$prevYearSum');
	//debugMsg($allYearSum,'$allYearSum');
	//debugMsg($fundInfo,'$fundInfo');

	$ret .= '<div class="project-summary">';

	$ret .= '<div class="thisyearprojects">'
		.'<span>รายรับ/รายจ่ายปีนี้</span>'
		.'<p>ยอดยกมา <span class="itemvalue">'.number_format($currentYearOpenBalance,2).'</span><span> บาท</span></p>'
		.'<p>รายรับ <span class="itemvalue">'.number_format($thisYearSum[4]->amount,2).'</span><span> บาท</span></p>'
		.'<p>รายจ่าย <span class="itemvalue">'.number_format($thisYearSum[5]->amount,2).'</span><span> บาท</span></p>'
		.'<p>คงเหลือ <span class="itemvalue">'.number_format($currentYearBalance,2).'</span><span> บาท</span></p>'
		.'</div>';

	$ret .= '<div class="lastyearprojects">'
		.'<span>รายรับ/รายจ่ายปีที่แล้ว</span>'
		.'<p>ยอดยกมา <span class="itemvalue">'.number_format($lastYearOpenBalance,2).'</span><span> บาท</span></p>'
		.'<p>รายรับ <span class="itemvalue">'.number_format($prevYearSum[4]->amount,2).'</span><span> บาท</span></p>'
		.'<p>รายจ่าย <span class="itemvalue">'.number_format($prevYearSum[5]->amount,2).'</span><span> บาท</span></p>'
		.'<p>คงเหลือ <span class="itemvalue">'.number_format($lastYearCloseBalance,2).'</span><span> บาท</span></p>'
		.'</div>';

	$ret .= '<div class="totalprojects">'
		.'<span>รายรับ/รายจ่ายทั้งหมด</span>'
		.'<p>ยอดยกมา <span class="itemvalue">'.number_format($openBalanceAmount,2).'</span><span> บาท</span></p>'
		.'<p>รายรับ <span class="itemvalue">'.number_format($allYearSum[4]->amount,2).'</span><span> บาท</span></p>'
		.'<p>รายจ่าย <span class="itemvalue">'.number_format($allYearSum[5]->amount,2).'</span><span> บาท</span></p>'
		.'<p>คงเหลือ <span class="itemvalue">'.number_format($currentDateBalance,2).'</span><span> บาท</span></p>'
		.'</div>';

	$ret .= '</div>';

	return $ret;
}
?>