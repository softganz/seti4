<?php
/**
* Project :: Local Fund Financial Summary By Quarter
* Created 2020-06-06
* Modify  2020-06-06
*
* @param Object $self
* @param Object $fundInfo
* @param Int $year
* @param Int $quarter
* @return String
*
* @usage project/fund/$orgId/financial.quarter/$year/$quarter
*/

$debug = true;

function project_fund_financial_quarter ($self, $fundInfo, $year, $quarter) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	if (empty($year) || empty($quarter)) return message('error', 'ไม่มีข้อมูลตามที่ระบุ');

	R::view('project.toolbar',$self,'ปิดงวดไตรมาส - '.$fundInfo->name,'fund',$fundInfo);

	$isAccess = $fundInfo->right->accessFinancial;

	if (!$isAccess) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$ret='';

	/*

	$current_quarter = ceil(sg_date($month.'-01','n') / 3);

	$firstdate = date('Y-m-d', strtotime(sg_date($month.'-01','Y') . '-' . (($current_quarter * 3) - 2) . '-1'));
	$lastdate = date('Y-m-t', strtotime(sg_date($month.'-01','Y') . '-' . (($current_quarter * 3)) . '-1'));

	$month = sg_date($lastdate,'Y-m');

	//$ret.='First date='.$firstdate.' '.$lastdate;
	$quarterYear = $year - ($quarter == 1 ? 1 : 0);
	$startBudgetDate = (sg_budget_year($month.'-01')-1).'-10-01';
	$endBudgetDate = sg_date($month.'-01','Y-m-t');
	$openBalanceQuarter = R::Model('project.fund.gl.balance',$fundInfo,$firstdate);
	$openBalanceYear = R::Model('project.fund.gl.balance',$fundInfo,$startBudgetDate);
	*/



	$quarterYear = $year - ($quarter == 1 ? 1 : 0);
	$startQuarterDate = $quarterYear.'-'.sprintf('%02d', $quarter == 1 ? '10' : $quarter * 3 - 5).'-01';
	$endQuarterDate = date('Y-m-t', strtotime($quarterYear.'-'.sprintf('%02d', $quarter == 1 ? '12' : $quarter * 3 - 3).'-01'));

	$startYearDate = (sg_budget_year($startQuarterDate)-1).'-10-01';
	$endBudgetDate = $endQuarterDate;
	$closeMonthDate = date('Y-m-d', strtotime(sg_date($startQuarterDate, 'Y-m').'-00'));
	$closeYearDate = date('Y-m-d', strtotime((sg_budget_year($startQuarterDate)-1).'-10-00'));

	$openBalanceYear = R::Model('project.fund.gl.balance', $fundInfo, $closeYearDate);
	$openBalanceQuarter = R::Model('project.fund.gl.balance', $fundInfo, $closeMonthDate);

	$ret = '<header class="header -box -hidden">'._HEADER_BACK.'<h3>รายงานการรับ-จ่ายและเงินคงเหลือประจำไตรมาส '.$quarter.' ปี '.($year+543).'</h3><nav class="nav"><a class="btn" href="'.url('project/fund/'.$orgId.'/financial.quarter/'.$year.'/'.$quarter).'" onclick="sgPrintPage(this.href);return false;"><i class="icon -material">print</i></a></nav></header>';

	//$ret .= '$startQuarterDate = '.$startQuarterDate.' $endQuarterDate = '.$endQuarterDate.'<br /> $startYearDate = '.$startYearDate.' $endBudgetDate = '.$endBudgetDate.'<br />$closeYearDate = '.$closeYearDate.' $closeMonthDate = '.$closeMonthDate.'<br />$openBalanceYear = '.$openBalanceYear.' $openBalanceQuarter = '.$openBalanceQuarter.'<br />';

	$ret.='<div class="project-report -forprint -financial-month">';
	$ret.='<header><h3>รายงานการรับ-จ่ายและเงินคงเหลือประจำเดือนกองทุนหลักประกันสุขภาพในระดับท้องถิ่นหรือพื้นที่</h3>';
	$ret.='<p>'.$fundInfo->name.' อำเภอ'.$fundInfo->info->nameampur.' จังหวัด'.$fundInfo->info->namechangwat.'</p>';
	$ret.='<p>ประจำไตรมาส '.($quarter).' เดือน'.sg_date($startQuarterDate,'ดดด ปปปป').' - '.sg_date($endQuarterDate,'ดดด ปปปป').' ปีงบประมาณ '.($year+543).'</p></header>';

	mydb::where('gl.`orgid` = :orgid AND LEFT(gl.`glcode`,1) IN (4,5)', ':orgid',$fundInfo->orgid);
	mydb::where('gl.`refdate` BETWEEN :startYearDate AND :endBudgetDate', ':startYearDate',$startYearDate, ':endBudgetDate',$endBudgetDate);
	mydb::where(NULL, ':startQuarterDate', $startQuarterDate, ':endQuarterDate', $endQuarterDate);

	$stmt = 'SELECT
			  gc.*
			, ABS(SUM(`amount`)) `totalYear`
			, ABS(SUM(IF(`refdate` BETWEEN :startQuarterDate AND :endQuarterDate,`amount`,0))) `totalMonth`
			, p.`glcode` `pglcode`
			, DATE_FORMAT(p.`refdate`,"%Y-%m") `refmonth`
			, p.`refdate`
			, p.`amount`
		FROM %glcode% gc
			LEFT JOIN
			(
			SELECT gl.`orgid`, gl.`glcode`, gl.`refdate`, gl.`amount`
				FROM %project_gl% gl
				%WHERE%
			) p ON p.`glcode` = `gc`.`glcode`
		WHERE `gltype` IN (4,5) AND `glparent` IS NOT NULL
		GROUP BY `glcode`
		ORDER BY `glcode`';

	$dbs = mydb::select($stmt);

	//$ret.=mydb()->_query;
	//$ret.=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->addClass('project-fund-funance-summary');
	$tables->thead=array('รายการ','money rev'=>'ไตรมาสนี้ (บาท)','money expense'=>'ทั้งปี (บาท)');


	$prevrs=NULL;

	$tables->rows[]=array('1. ยอดยกมาจากปีที่แล้ว',number_format($openBalanceQuarter,2),number_format($openBalanceYear,2),'config'=>array('class'=>'subheader'));
	$tables->rows[]=array('','','');
	$tables->rows[]=array('2. รายการรับ','','','config'=>array('class'=>'subheader'));
	$monthRcvTotal=$monthExpTotal=0;
	$yearRcvTotal=$yearExpTotal=0;
	foreach ($dbs->items as $rs) {
		if ($rs->gltype!=4) continue;

		$tables->rows[] = array(
			$rs->glname,
			number_format($rs->totalMonth,2),
			number_format($rs->totalYear,2),
		);

		$monthRcvTotal+=$rs->totalMonth;
		$yearRcvTotal+=$rs->totalYear;
	}
	$tables->rows[] = array(
		'รวมรายรับ',
		number_format($monthRcvTotal,2),
		number_format($yearRcvTotal,2),
		'config'=>array('class'=>'subfooter')
	);

	$tables->rows[]=array('','','');

	$tables->rows[]=array('3. รายการจ่าย','','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		if ($rs->gltype!=5) continue;

		$tables->rows[] = array(
			$rs->glname,
			number_format($rs->totalMonth,2),
			number_format($rs->totalYear,2),
		);

		$monthExpTotal+=$rs->totalMonth;
		$yearExpTotal+=$rs->totalYear;
	}
	$tables->rows[] = array(
		'รวมรายจ่าย',
		number_format($monthExpTotal,2),
		number_format($yearExpTotal,2),
		'config'=>array('class'=>'subfooter')
	);

	$tables->rows[]=array('','','');

	$tables->rows[] = array(
		'คงเหลือยกไป',
		number_format($openBalanceQuarter+$monthRcvTotal-$monthExpTotal,2),
		number_format($openBalanceYear+$yearRcvTotal-$yearExpTotal,2),
		'config'=>array('class'=>'subfooter')
	);

	$ret.=$tables->build();

	$ret.='<p>เรียน คณะกรรมการ '.$fundInfo->name.'</p>';
	$ret.='<p>เพื่อเห็นชอบ</p>';
	$ret.='<p class="sign">(ลงชื่อ) ...................................................... ผู้จัดทำ<br />(......................................................)<br />เจ้าหน้าที่ผู้รับผิดชอบ จัดทำการเงินและบัญชี</p>';
	$ret.='<p class="sign">(ลงชื่อ) ...................................................... ผู้รายงาน<br />(......................................................)<br />......................................................</p>';
	$ret.='<p class="clear">เห็นชอบ ตามมติที่ประชุมคณะกรรมการ ครั้งที่ ...../ปี ........</p>';
	$ret .= '<div class="-sg-clearfix">';
	$ret.='<p class="sign">(ลงชื่อ) ............................................... ผู้รับผิดชอบ<br />(......................................................)<br />ประธานกรรมการกองทุนฯ</p>';
	$ret.='<p class="sign -sg-clearfix"></p>';
	$ret.='</div>';

	$ret.='</div>';
	//$ret.=print_o($fundInfo,'$fundInfo');
	$ret.='<style type="text/css">
	.project-report header {text-align:center; font-weight:bold;}
	.project-report.-financial-month p.sign {width:50%; margin:2em 0; padding:0; float:left;text-align:center;}
	@media print {
		.project-summary {display:none;}
		.item {display: table; width:100%;}
		.col-money {text-align: right;}
		.subheader,.subfooter {font-weight: bold;}
	}
	</style>';
	return $ret;
}
?>