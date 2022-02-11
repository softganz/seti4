<?php
/**
* Project :: Local Fund Financial Summary By Year
* Created 2020-06-06
* Modify  2020-06-06
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @usage project/fund/$orgId/financial.year/$budgetYear
*/

$debug = true;

function project_fund_financial_year ($self, $fundInfo, $budgetYear) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	if (empty($budgetYear)) return message('error', 'ไม่มีข้อมูลตามที่ระบุ');

	R::view('project.toolbar',$self,'รายงานการรับ-จ่ายประจำปี '.($budgetYear+543).' - '.$fundInfo->name,'fund',$fundInfo);

	$isEdit = $fundInfo->right->editFinancial;
	$isAccess = $fundInfo->right->accessFinancial;

	if (!$isAccess) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$startdate = ($budgetYear-1).'-10-01';
	$enddate = $budgetYear.'-09-30';
	$closeYearDate = ($budgetYear-1).'-09-30';

	$openbalanceYear = R::Model('project.fund.gl.balance',$fundInfo,$closeYearDate);

	$ret = '<header class="header -box -hidden">'._HEADER_BACK.'<h3>รายงานการรับ-จ่ายและเงินคงเหลือประจำปี '.($budgetYear+543).'</h3><nav class="nav"><a class="btn" href="'.url('project/fund/'.$orgId.'/financial.year/'.$budgetYear).'" onclick="sgPrintPage(this.href);return false;"><i class="icon -material">print</i></a></nav></header>';

	$ret .= '<div class="project-report -forprint -financial-month">';
	$ret .= '<header><h3>รายงานการรับ-จ่ายและเงินคงเหลือประจำปีกองทุนหลักประกันสุขภาพในระดับท้องถิ่นหรือพื้นที่</h3>';
	$ret .= '<p>'.$fundInfo->name.' อำเภอ'.$fundInfo->info->nameampur.' จังหวัด'.$fundInfo->info->namechangwat.'</p>';
	$ret .= '<p>ประจำปีงบประมาณ '.($budgetYear+543).' เดือน'.sg_date($startdate,'ดดด ปปปป').' - '.sg_date($enddate,'ดดด ปปปป').'</p></header>';

	mydb::where('gl.`orgid` = :orgid AND LEFT(gl.`glcode`,1) IN (4,5)', ':orgid', $fundInfo->orgid);
	mydb::where('gl.`refdate` BETWEEN :startdate AND :enddate', ':startdate', $startdate, ':enddate',$enddate);

	$stmt='SELECT
			  gc.*
			, ABS(SUM(`amount`)) `totalYear`
			, p.`orgid`
			, DATE_FORMAT(p.`refdate`,"%Y") `refyear`
		FROM %glcode% gc
			LEFT JOIN
			(
			SELECT gl.`orgid`, gl.`glcode`, gl.`refdate`, gl.`amount`
				FROM %project_gl% gl
				%WHERE%
			) p ON p.`glcode` = gc.`glcode`
		WHERE `gltype` IN (4,5) AND `glparent` IS NOT NULL
		GROUP BY `glcode`
		ORDER BY `glcode`';

	$dbs=mydb::select($stmt,$where['value']);
	//$ret.=mydb()->_query;
	//$ret.=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->addClass('project-fund-funance-summary');
	$tables->thead=array('รายการ','money expense'=>'ทั้งปี (บาท)');



	$tables->rows[]=array('1. ยอดยกมาจากปีที่แล้ว',number_format($openbalanceYear,2),'config'=>array('class'=>'subheader'));
	$tables->rows[]=array('','','');
	$tables->rows[]=array('2. รายการรับ','','config'=>array('class'=>'subheader'));
	$yearRcvTotal=$yearExpTotal=0;
	foreach ($dbs->items as $rs) {
		if ($rs->gltype!=4) continue;

		$tables->rows[]=array(
			$rs->glname,
			number_format($rs->totalYear,2),
		);
		$yearRcvTotal+=$rs->totalYear;
	}

	$tables->rows[]=array(
		'รวมรายรับ',
		number_format($yearRcvTotal,2),
		'config'=>array('class'=>'subfooter')
	);

	$tables->rows[]=array('','');

	$tables->rows[]=array('3. รายการจ่าย','','config'=>array('class'=>'subheader'));
	foreach ($dbs->items as $rs) {
		if ($rs->gltype!=5) continue;

		$tables->rows[]=array(
			$rs->glname,
			number_format($rs->totalYear,2),
		);
		$yearExpTotal+=$rs->totalYear;
	}

	$tables->rows[]=array(
		'รวมรายจ่าย',
		number_format($yearExpTotal,2),
		'config'=>array('class'=>'subfooter')
	);
	$tables->rows[]=array('','');

	$tables->rows[]=array(
		'คงเหลือยกไป',
		number_format($openbalanceYear+$yearRcvTotal-$yearExpTotal,2),
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