<?php
/**
* OBT Report Balance
* Created 2019-05-23
* Modify  2019-05-23
*
* @param Object $self
* @param Int $fundCode
* @return String
*/

$debug = true;

function project_obt_report_balance($self, $orgId = NULL) {
	$fundInfo = R::Model('project.fund.get', $orgId);
	$orgId = $fundInfo->orgid;

	if (!$fundInfo) return message('error', 'Invalid Information of '.$fundCode);

	$updateBalanceResult = R::Model('project.obt.balance.update', $fundInfo, '{debug: true}');


	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead = array(
		'เดือน',
		'money -rcv' => 'รายรับ (บาท)',
		'money -exp' => 'รายจ่าย (บาท)',
		'money -balance' => 'คงเหลือ (บาท)',
		'center -1m' => '1M',
		'center -3m' => '3M',
		'center -1y' => '1Y',
		'center -lock' => '',
	);

	$tables->rows[]=array('ยอดยกมา','','',number_format($fundInfo->info->openbalance,2),'','','','');

	$monthRcvTotal = $monthExpTotal = 0;
	$balance = $fundInfo->info->openbalance;
	$currentMonth = date('Y-m');
	$fincloseMonth = $fundInfo->finclosemonth ? sg_date($fundInfo->finclosemonth,'Y-m') : '';
	//$ret.='current='.$currentMonth.' closed='.$fincloseMonth;
	$no = 0;
	foreach ($updateBalanceResult->trans as $rs) {
		$no++;
		$lockIcon = '';
		if ($rs->MONTH_REF <= $fincloseMonth) {
			$lockIcon = '<i class="icon -lock -gray"></i>';
		} else if ($rs->MONTH_REF<$currentMonth) {
			$lockIcon = '<i class="icon -unlock"></i>';
		}
		$balance = $balance + $rs->REVENUE - $rs->EXPENDITURE;
		$tables->rows[] = array(
			sg_date($rs->MONTH_REF.'-01','ดดด ปปปป'),
			number_format($rs->REVENUE,2),
			number_format($rs->EXPENDITURE,2),
			number_format($rs->BALANCE_FORWARD,2),
			'<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial.month/'.$rs->MONTH_REF).'"data-rel="box"><i class="icon -material">find_in_page</i></a>',
			in_array(substr($rs->MONTH_REF,5,2),array('12','03','06','09'))?'<a class="sg-action -disabled" href="'.url('project/fund/'.$orgId.'/financial.quarter/'.$rs->MONTH_REF).'"data-rel="box"><i class="icon -material -gray">find_in_page</i></a>':'',
			substr($rs->MONTH_REF,5,2)=='09'?'<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial.year/'.substr($rs->MONTH_REF,0,4)).'"data-rel="box"><i class="icon -material">find_in_page</i></a>':'',
			$lockIcon,
		);
		$monthRcvTotal += $rs->total;
	}

	$tables->tfoot[] = array(
		'รวมเงิน',
		number_format($updateBalanceResult->revenue,2),
		number_format($updateBalanceResult->expenditure,2),
		number_format($updateBalanceResult->balance,2),
		'',
		'',
		'',
		'',
	);

	$ret .= $tables->build();

	//$ret .= print_o($updateBalanceResult,'$result');
	//$ret.=print_o($fundInfo,'$fundInfo');
	return $ret;
}
?>