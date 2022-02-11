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

function project_api_fund_balance($self, $orgId = NULL) {
	//$getYear = intval(SG\getFirst(post('yy'), date('Y')));
	//$getMonth = intval(post('mm'));
	$getOrg = SG\getFirst($orgId, post('org'));

	$result = NULL;

	//$isRight = is_admin() || post('token') == 'ASDWE9283dEkfkdieDKFLKDK';

	//if (!$isRight) return message('error', 'Access Denied');


	$fundInfo = R::Model('project.fund.get', $getOrg);

	if (empty($getOrg)) return $ret . message('notify', 'กรุณาระบุกองทุน');

	$fundBalance = R::Model('project.fund.balance', $fundInfo);



	$result->orgId = $fundBalance->orgId;
	$result->fundId = $fundBalance->fundId;
	$result->fundName = $fundBalance->fundName;
	$result->areaId = $fundBalance->areaId;
	$result->numRows = count($fundBalance->balance);
	$result->openBalance = $fundBalance->openBalance;
	$result->totalRevenue = $fundBalance->totalRevenue;
	$result->totalExpend = $fundBalance->totalExpend;
	$result->closeBalance = $fundBalance->closeBalance;

	foreach ($fundBalance->balance as $rs) {
		$rs->amount = (float) $rs->amount;
		$rs->fundid = (string) $rs->fundid;

		$reportId = (String) ($rs->budgetYear*10000000 + $rs->budgetMonth*100000 + $rs->orgid);

		$result->rows[] = (Object) array(
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

	//die(json_encode($result));

	return $result;
}
?>