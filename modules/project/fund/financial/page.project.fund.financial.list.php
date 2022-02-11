<?php
/**
* Project :: Local Fund Financial List All Month
* Created 2020-06-06
* Modify  2020-06-06
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @call project/fund/$orgId/financial.list
*/

$debug = true;

function project_fund_financial_list($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isEdit = $fundInfo->right->editFinancial;
	$isAccess = $fundInfo->right->accessFinancial;

	if (!$isAccess) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$openBalance = $fundInfo->info->openbalance;

	mydb::where('gc.`gltype` IN (4,5) AND gl.`refdate` >= :openbaldate', ':openbaldate' , $fundInfo->info->openbaldate);
	mydb::where('gl.`orgid` = :orgid',':orgid',$fundInfo->orgid);

	$stmt = 'SELECT
		  gl.`orgid`
		, DATE_FORMAT(gl.`refdate`,"%Y-%m") `refmonth`
		, YEAR(gl.`refdate`) + IF(MONTH(gl.`refdate`)>=10,1,0) `budgetYear`
		, CASE
			WHEN MONTH(gl.`refdate`)>=10 THEN 1
			WHEN MONTH(gl.`refdate`)>=7 THEN 4
			WHEN MONTH(gl.`refdate`)>=4 THEN 3
			WHEN MONTH(gl.`refdate`)>=1 THEN 2
		END `budgetQuarter`
		, ABS(SUM(IF(gc.`gltype`=4,gl.`amount`,0))) `rcvAmount`
		, ABS(SUM(IF(gc.`gltype`=5,gl.`amount`,0))) `expAmount`
		FROM %glcode% gc
			LEFT JOIN %project_gl% gl USING(`glcode`)
		%WHERE%
		GROUP BY `refmonth`
		ORDER BY `refmonth` ASC;
		-- {key:"refmonth",sum:"rcvAmount,expAmount"}';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//$ret .= print_o($dbs,'$dbs');

//123 = 1 456 = 2 789 = 3
	// Insert empty month from openbalancedate to now
	$startDate = $fundInfo->info->openbaldate;
	$endDate = date('Y-m-d');
	while (strtotime($startDate) <= strtotime($endDate)) {
		$month = sg_date($startDate, 'Y-m');
		//debugMsg('$month = '.$month);
		if (!isset($dbs->items[$month])) {
			$dbs->items[$month] = (Object) Array(
				'orgid' => $fundInfo->orgid,
				'refmonth' => sg_date($startDate, 'Y-m'),
				'budgetYear' => sg_budget_year($startDate),
				'budgetQuarter' => sg_date($startDate, 'm') >= 10 ? 1 : floor((sg_date($startDate, 'm') - 1)/3) + 2,
				'rcvAmount' => 0,
				'expAmount' => 0,
			);
			//debugMsg('$startDate = '.$startDate.' '.'DATE = '.sg_date($startDate,'Y-m-d'));
		}
		$startDate = date('Y-m-d', strtotime($startDate.
		'+ 1 month'));
	}
	ksort($dbs->items);

	//$ret.=print_o($dbs,'$dbs');


	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead = array(
		'เดือน',
		'money rev' => 'รายรับ (บาท)',
		'money expense' => 'รายจ่าย (บาท)',
		'money -balance' => 'คงเหลือ (บาท)',
		'center -view' => '1M',
		'3M',
		'1Y',
		'center -lock' => ''
	);

	$tables->rows[] = array('ยอดยกมา','','',number_format($openBalance,2),'','','','');

	$monthRcvTotal = $monthExpTotal = 0;
	$balance = $openBalance;
	$currentMonth = date('Y-m');
	$fincloseMonth = $fundInfo->finclosemonth ? sg_date($fundInfo->finclosemonth,'Y-m') : '';
	$no = 0;

	foreach ($dbs->items as $rs) {
		$no++;
		$lockIcon = '';
		if ($isEdit) {
			if ($rs->refmonth <= $fincloseMonth) {
				$lockIcon = '<a class="sg-action" href="'.url('project/fund/'.$orgId.'/info/financial.unlock/'.$rs->refmonth).'" data-rel="notify" data-done="load:#project-financial-info:'.url('project/fund/'.$orgId.'/financial.list').'" title="คลิกเพื่อปลดล็อคงวดเดือน"><i class="icon -material -gray">lock</i></a>';
			} else if ($rs->refmonth < $currentMonth) {
				$lockIcon = '<a class="sg-action" href="'.url('project/fund/'.$orgId.'/info/financial.lock/'.$rs->refmonth).'" data-rel="notify" data-done="load:#project-financial-info:'.url('project/fund/'.$orgId.'/financial.list').'" title="คลิกเพื่อล็อคงวดเดือน"><i class="icon -material">lock_open</i></a>';
			}
		} else {
			if ($rs->refmonth <= $fincloseMonth) {
				$lockIcon = '<i class="icon -material -gray">lock</i>';
			} else if ($rs->refmonth < $currentMonth) {
				$lockIcon = '<i class="icon -material -gray">lock_open</i>';
			}
		}

		$balance = $balance + $rs->rcvAmount - $rs->expAmount;
		$tables->rows[] = array(
			sg_date($rs->refmonth.'-01','ดดด ปปปป'),
			number_format($rs->rcvAmount,2),
			number_format($rs->expAmount,2),
			number_format($balance,2),
			'<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial.month/'.$rs->refmonth).'"data-rel="box" data-width="800"><i class="icon -material">find_in_page</i></a>',
			in_array(substr($rs->refmonth,5,2),array('12','03','06','09'))?'<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial.quarter/'.$rs->budgetYear.'/'.$rs->budgetQuarter).'"data-rel="box" data-width="800"><i class="icon -material">find_in_page</i></a>':'',
			substr($rs->refmonth,5,2)=='09'?'<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial.year/'.$rs->budgetYear).'"data-rel="box" data-width="800"><i class="icon -material">find_in_page</i></a>':'',
			$lockIcon,
		);
		$monthRcvTotal += $rs->total;
	}

	$tables->tfoot[] = array(
		'รวมเงิน',
		number_format($dbs->sum->rcvAmount,2),
		number_format($dbs->sum->expAmount,2),
		number_format($balance,2),
		'',
		'',
		'',
		'',
	);

	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');
	//$ret.=print_o($fundInfo,'$fundInfo');
	return $ret;
}
?>