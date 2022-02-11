<?php
/**
* Project NHSO Money Balance for Export to OBT
*
* @param Object $self
* @return String/JSON/EXCEL
*
* NHSO Data Structure
* REPORT_ID : รหัสรายการ
* FUND_CODE : รหัสกองทุน
* BUDGET_YEAR : ปีงบประมาณ
* MONTH_ID : เดือน (1-12)
* BALANCE_FORWARD : ยอดเงินคงเหลือยกมา
* REVENUE : รายรับ
* EXPENDITURE : รายจ่าย
*/

$debug = true;

function project_nhso_org_balance($self, $orgId = NULL) {
	$getYear = intval(SG\getFirst(post('yy'), date('Y')));
	$getMonth = intval(post('mm'));
	$getOrg = SG\getFirst($orgId, post('org'));
	$formatOutput = post('fmt');

	$ret = '';

	$isRight = is_admin() || post('token') == 'ASDWE9283dEkfkdieDKFLKDK';

	if (!$isRight) return message('error', 'Access Denied');

	// ปีงบประมาณ 2562 => 2561-10 to 2562-09 => 2018 => 2018-10 - 2019-09
	// ปีงบประมาณ 2563 => 2562-10 to 2563-09 => 2019 => 2019-10 - 2020-09

	/*
	$realYear = $getYear;
	if ($getMonth) {
		$realYear = $getMonth >= 10 ? $getYear-1 : $getYear;
		$startDate = ($realYear).'-'.sprintf('%02d',$getMonth).'-01';
		$endDate = date('Y-m-t', strtotime($startDate));
		$closeDate = date('Y-m-d', strtotime($realYear.'-'.sprintf('%02d',$getMonth).'-00'));
	} else {
		$startDate = ($getYear - 1).'-10-01';
		$endDate = $getYear.'-09-30';
		$closeDate = date('Y-m-d', strtotime(($getYear-1).'-10-00'));
	}
	*/

	$repTitle = 'สปสช. - ยอดคงเหลือกองทุน (DRAFT)';


	R::View('project.toolbar',$self, $repTitle, 'fund');




	$fundInfo = R::Model('project.fund.get', $getOrg);

	if (empty($getOrg)) return $ret . message('notify', 'กรุณาระบุกองทุน');

	$fundBalance = R::Model('project.fund.balance', $fundInfo);




	$tables = new Table();
	$tables->thead = array(
		'รหัสกองทุน',
		'year -amt' => 'ปีงบประมาณ',
		'refmonth -date' => 'เดือน',
		'openbal -money' => 'ยอดยกมา',
		'revenue -money' => 'รายรับ',
		'expend -money' => 'รายจ่าย',
		'closebal -money' => 'ยอดคงเหลือ',
	);

	$exports->numrows = count($fundBalance->balance);

	foreach ($fundBalance->balance as $rs) {
		$rs->amount = (float) $rs->amount;
		$rs->fundid = (string) $rs->fundid;

		$tables->rows[] = array(
			$fundInfo->fundid.' - '.$fundInfo->name,
			$rs->budgetYear + 543,
			$rs->budgetMonth,
			number_format($rs->openBalance, 2),
			number_format($rs->revenue, 2),
			number_format($rs->expend,2),
			number_format($rs->closeBalance,2),
		);

		$reportId = (String) ($rs->budgetYear*10000000 + $rs->budgetMonth*100000 + $rs->orgid);

		$exports->rows[] = (Object) array(
			'reportId' => $reportId,
			'fundCode' => $fundInfo->fundid,
			'orgid' => $fundInfo->orgid,
			'budgetYear' => $rs->budgetYear,
			'monthId' => intval($rs->budgetMonth),
			'balanceForward' => $rs->closeBalance,
			'Revenue' => $rs->revenue,
			'Expenditure' => $rs->expend
		);
	}

	$tables->tfoot[] = array(
		'รวม',
		'',
		'',
		'',
		number_format($fundBalance->totalRevenue,2),
		number_format($fundBalance->totalExpend,2),
		''
	);

	$ret .= $tables->build();

	if ($formatOutput == 'rest') {
		die(json_encode($exports));
	}

	$ret .= print_o($fundBalance, '$fundBalance');
	//$ret .= print_o($fundInfo,'$fundInfo');

	return $ret;
}
?>