<?php
/**
* Get Finance Fund Balance
* Created 2017-02-19
* Modify  2020-04-28
*
* @param Object $fundInfo
* @param String $balanceToDate
* @return Decimal
*/

$debug = true;

function r_project_fund_gl_balance($fundInfo, $balanceToDate = NULL) {
	$orgId = $fundInfo->orgid;
	$balance = $fundInfo->info->openbalance;
	$openBalanceDate = $fundInfo->info->openbaldate;

	/*
	$stmt = 'SELECT
			SUM(gl.`amount`) `balance`
		FROM %project_gl% gl
			LEFT JOIN %glcode% gc USING(`glcode`)
		WHERE gl.`orgid` = :orgid AND gc.`gltype` IN (4,5) AND gl.`refdate`>= :opendate AND gl.`refdate` < :balancedate
		LIMIT 1';
	*/

	mydb::where('gl.`orgid` = :orgid AND gc.`gltype` IN (4,5)', ':orgid', $orgId);

	if ($balanceToDate) {
		// Get Balance At End Of Date
		mydb::where('gl.`refdate` BETWEEN :opendate AND :balancedate', ':opendate', $openBalanceDate, ':balancedate', $balanceToDate);
	} else {
		// Get Current Balance
		mydb::where('gl.`refdate` >= :opendate', ':opendate', $openBalanceDate);
	}

	$stmt = 'SELECT
			SUM(gl.`amount`) `paidAmt`
		FROM %project_gl% gl
			LEFT JOIN %glcode% gc USING(`glcode`)
		%WHERE% 
		LIMIT 1';

	$rs = mydb::select($stmt);

	$balance -= $rs->paidAmt;

	return $balance;
}
?>