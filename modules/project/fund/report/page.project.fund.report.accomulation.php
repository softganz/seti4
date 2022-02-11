<?php
/**
* Project :: Fund Report Accomulation
* Created 2018-06-17
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/accomulation
*/

$debug = true;

function project_fund_report_accomulation($self) {
	$yearFrom = SG\getFirst(post('yrfr'),date('Y')-1);
	$yearTo = SG\getFirst(post('yrto'),date('Y'));
	$area = post('area');
	$prov = post('prov');
	$ampur = post('ampur');
	$graphSummary = post('gr') == 1;

	$yearFrom1 = sg_date(($yearFrom-1).'-01-01','ปป');
	$yearFrom2 = sg_date($yearFrom.'-01-01','ปป');
	$yearTo1 = sg_date(($yearTo-1).'-01-01','ปป');
	$yearTo2 = sg_date($yearTo.'-01-01','ปป');

	$repTitle='รายงานสรุปการใช้จ่ายสะสมรายเดือน (Accomulation)';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui = new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/accomulation').'">'.$repTitle.'</a>');
	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" action="'.url('project/fund/report/accomulation').'" method="get">';
	$form.='<span>ตัวเลือก </span>';

	// Select year
	$stmt='SELECT DISTINCT YEAR(`refdate`)+IF(MONTH(`refdate`)>=10,1,0) `budgetYear` FROM %project_gl% HAVING `budgetYear` ORDER BY `budgetYear` ASC';
	$yearList=mydb::select($stmt);

	$form.='<select id="year" class="form-select" name="yrfr">';
	//$form.='<option value="">ทุกปีงบประมาณ</option>';
	foreach ($yearList->items as $rs) {
		$form.='<option value="'.$rs->budgetYear.'" '.($rs->budgetYear==$yearFrom?'selected="selected"':'').'>พ.ศ.'.($rs->budgetYear+543).'</option>';
	}
	$form.='</select> ';

	$form.=' - <select id="year" class="form-select" name="yrto">';
	//$form.='<option value="">ทุกปีงบประมาณ</option>';
	foreach ($yearList->items as $rs) {
		$form.='<option value="'.$rs->budgetYear.'" '.($rs->budgetYear==$yearTo?'selected="selected"':'').'>พ.ศ.'.($rs->budgetYear+543).'</option>';
	}
	$form.='</select> ';

	// Select area
	$form.='<select id="area" class="form-select" name="area">';
	$form.='<option value="">ทุกเขต</option>';
	$areaList=mydb::select('SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype`="nhso" ORDER BY `areaid`+0 ASC');
	foreach ($areaList->items as $rs) {
		$form.='<option value="'.$rs->areaid.'" '.($rs->areaid==$area?'selected="selected"':'').'>เขต '.$rs->areaid.' '.$rs->areaname.'</option>';
	}
	$form.='</select> ';

	// Select province
	if ($area) {
		$stmt='SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid`=:areaid HAVING `provname` != ""';
		$provList=mydb::select($stmt,':areaid',$area);
		$form.='<select id="province" class="form-select" name="prov">';
		$form.='<option value="">ทุกจังหวัด</option>';
		foreach ($provList->items as $rs) {
			$form.='<option value="'.$rs->changwat.'" '.($rs->changwat==$prov?'selected="selected"':'').'>'.$rs->provname.'</option>';
		}
		$form.='</select> ';
	}

	// Select province
	if ($prov) {
		$stmt='SELECT DISTINCT `distid`, `distname` FROM  %co_district% WHERE LEFT(`distid`,2)=:prov';
		$ampurList=mydb::select($stmt,':prov',$prov);
		$form.='<select id="ampur" class="form-select" name="ampur">';
		$form.='<option value="">ทุกอำเภอ</option>';
		foreach ($ampurList->items as $rs) {
			$form.='<option value="'.substr($rs->distid,2).'" '.(substr($rs->distid,2)==$ampur?'selected="selected"':'').'>'.$rs->distname.'</option>';
		}
		$form.='</select> ';
	}
	$form.='<label><input type="checkbox" name="gr" value="1" '.($graphSummary==1?'checked="checked"':'').' />แสดงกราฟผลรวม</label>';

	$form.='</form>'._NL;

	$ret.=$form;

	$label='CONCAT("เขต ",LPAD(a.areaid,2," ")," ",a.`areaname`)';
	if ($yearTo) {
		mydb::where(NULL,':year',$yearTo-1,':nextyear',$yearTo+0);
	} else {
		mydb::where(NULL,':startdate','2016-10-01',':enddate',date('Y').'-09-30',':closebalancedate',(date('Y')-1).'-09-30');
	}
	if ($area) {
	 mydb::where('f.`areaid`=:areaid',':areaid',$area);
	 $label='f.`namechangwat`';
	}
	if ($ampur) {
		mydb::where('f.`changwat`=:prov AND f.`ampur`=:ampur',':prov',$prov,':ampur',$ampur);
		$label='f.`fundname`';
	} else if ($prov) {
		mydb::where('f.`changwat`=:prov',':prov',$prov);
		$label='f.`nameampur`';
	}

	$stmt="
		SELECT
			  $label `label`
			,	f.*
			, SUM(`total10`) `total10`
			, SUM(`total11`) `total11`
			, SUM(`total12`) `total12`
			, SUM(`total01`) `total01`
			, SUM(`total02`) `total02`
			, SUM(`total03`) `total03`
			, SUM(`total04`) `total04`
			, SUM(`total05`) `total05`
			, SUM(`total06`) `total06`
			, SUM(`total07`) `total07`
			, SUM(`total08`) `total08`
			, SUM(`total09`) `total09`
			, SUM(`totalPrev10`) `totalPrev10`
			, SUM(`totalPrev11`) `totalPrev11`
			, SUM(`totalPrev12`) `totalPrev12`
			, SUM(`totalPrev01`) `totalPrev01`
			, SUM(`totalPrev02`) `totalPrev02`
			, SUM(`totalPrev03`) `totalPrev03`
			, SUM(`totalPrev04`) `totalPrev04`
			, SUM(`totalPrev05`) `totalPrev05`
			, SUM(`totalPrev06`) `totalPrev06`
			, SUM(`totalPrev07`) `totalPrev07`
			, SUM(`totalPrev08`) `totalPrev08`
			, SUM(`totalPrev09`) `totalPrev09`
			FROM
			(
				SELECT
					  f.`orgid`
					, f.`areaid`
					, f.`changwat`
					, f.`ampur`
					, f.`namechangwat`
					, f.`nameampur`
					, o.`name` `fundname`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :year AND MONTH(g.`refdate`)=10,g.`amount`,0))) `total10`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :year AND MONTH(g.`refdate`)=11,g.`amount`,0))) `total11`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :year AND MONTH(g.`refdate`)=12,g.`amount`,0))) `total12`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear AND MONTH(g.`refdate`)=1,g.`amount`,0))) `total01`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear AND MONTH(g.`refdate`)=2,g.`amount`,0))) `total02`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear AND MONTH(g.`refdate`)=3,g.`amount`,0))) `total03`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear AND MONTH(g.`refdate`)=4,g.`amount`,0))) `total04`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear AND MONTH(g.`refdate`)=5,g.`amount`,0))) `total05`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear AND MONTH(g.`refdate`)=6,g.`amount`,0))) `total06`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear AND MONTH(g.`refdate`)=7,g.`amount`,0))) `total07`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear AND MONTH(g.`refdate`)=8,g.`amount`,0))) `total08`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear AND MONTH(g.`refdate`)=9,g.`amount`,0))) `total09`

					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = 2016 AND MONTH(g.`refdate`)=10,g.`amount`,0))) `totalPrev10`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :year - 1 AND MONTH(g.`refdate`)=11,g.`amount`,0))) `totalPrev11`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :year - 1 AND MONTH(g.`refdate`)=12,g.`amount`,0))) `totalPrev12`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear - 1 AND MONTH(g.`refdate`)=1,g.`amount`,0))) `totalPrev01`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear - 1 AND MONTH(g.`refdate`)=2,g.`amount`,0))) `totalPrev02`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear - 1 AND MONTH(g.`refdate`)=3,g.`amount`,0))) `totalPrev03`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear - 1 AND MONTH(g.`refdate`)=4,g.`amount`,0))) `totalPrev04`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear - 1 AND MONTH(g.`refdate`)=5,g.`amount`,0))) `totalPrev05`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear - 1 AND MONTH(g.`refdate`)=6,g.`amount`,0))) `totalPrev06`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear - 1 AND MONTH(g.`refdate`)=7,g.`amount`,0))) `totalPrev07`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear - 1 AND MONTH(g.`refdate`)=8,g.`amount`,0))) `totalPrev08`
					, ABS(SUM(IF(gc.`gltype`='5' AND YEAR(g.`refdate`) = :nextyear - 1 AND MONTH(g.`refdate`)=9,g.`amount`,0))) `totalPrev09`

				FROM %project_fund% f
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_gl% g ON g.`orgid`=f.`orgid`
					LEFT JOIN %glcode% gc USING(`glcode`)
				GROUP BY `orgid`
			) f
				RIGHT JOIN %project_area% a USING(`areaid`)
			%WHERE%
			GROUP BY `label`
			ORDER BY CONVERT(`label` USING tis620) ASC
			;
			-- {sum:\"total10,total11,total12,total01,total02,total03,total04,total05,total06,total07,total08,total09\"}
			";


	$dbs=mydb::select($stmt);
	//$ret.='<pre>'.mydb()->_query.'</pre><br />';
	//$ret.=mydb::printtable($dbs);
	//$ret.=print_o($dbs);


	$tables = new Table();
	$tables->addClass('-nowrap');
	$tables->thead=array('no'=>'ลำดับ','พื้นที่','money -m10'=>'ต.ค.'.$yearTo1,'money -m11'=>'พ.ย.'.$yearTo1,'money -m12'=>'ธ.ค.'.$yearTo1,'money -m01'=>'ม.ค.'.$yearTo2,'money -m02'=>'ก.พ.'.$yearTo2,'money -m03'=>'มี.ค.'.$yearTo2,'money -m04'=>'เม.ย.'.$yearTo2,'money -m05'=>'พ.ค.'.$yearTo2,'money -m06'=>'มิ.ย.'.$yearTo2,'money -m07'=>'ก.ค.'.$yearTo2,'money -m08'=>'ส.ค.'.$yearTo2,'money -m09'=>'ก.ย.'.$yearTo2);

	$chartTable = new Table();
	$chartTable->thead=array('พื้นที่','amt -thin'=>'ต.ค.','','พ.ย.','','amt -fat'=>'ธ.ค.','','ม.ค.','');
	$chartTable->thead=array('พื้นที่');
	foreach ($dbs->items as $rs) {
		$chartTable->thead[]=$rs->label;
		$chartTable->thead[]='';
	}

	$sumTotalMonth10 = $dbs->sum->total10;
	$sumTotalMonth11 = $sumTotalMonth10 + $dbs->sum->total11;
	$sumTotalMonth12 = $sumTotalMonth11 + $dbs->sum->total12;
	$sumTotalMonth01 = $sumTotalMonth12 + $dbs->sum->total01;
	$sumTotalMonth02 = $sumTotalMonth01 + $dbs->sum->total02;
	$sumTotalMonth03 = $sumTotalMonth02 + $dbs->sum->total03;
	$sumTotalMonth04 = $sumTotalMonth03 + $dbs->sum->total04;
	$sumTotalMonth05 = $sumTotalMonth04 + $dbs->sum->total05;
	$sumTotalMonth06 = $sumTotalMonth05 + $dbs->sum->total06;
	$sumTotalMonth07 = $sumTotalMonth06 + $dbs->sum->total07;
	$sumTotalMonth08 = $sumTotalMonth07 + $dbs->sum->total08;
	$sumTotalMonth09 = $sumTotalMonth08 + $dbs->sum->total09;

	foreach ($dbs->items as $rs) {
		$totalMonth10 = $rs->total10;
		$totalMonth11 = $totalMonth10 + $rs->total11;
		$totalMonth12 = $totalMonth11 + $rs->total12;
		$totalMonth01 = $totalMonth12 + $rs->total01;
		$totalMonth02 = $totalMonth01 + $rs->total02;
		$totalMonth03 = $totalMonth02 + $rs->total03;
		$totalMonth04 = $totalMonth03 + $rs->total04;
		$totalMonth05 = $totalMonth04 + $rs->total05;
		$totalMonth06 = $totalMonth05 + $rs->total06;
		$totalMonth07 = $totalMonth06 + $rs->total07;
		$totalMonth08 = $totalMonth07 + $rs->total08;
		$totalMonth09 = $totalMonth08 + $rs->total09;

		$totalPrevMonth10 = $rs->total10;
		$totalPrevMonth11 = $totalPrevMonth10 + $rs->totalPrev11;
		$totalPrevMonth12 = $totalPrevMonth11 + $rs->totalPrev12;
		$totalPrevMonth01 = $totalPrevMonth12 + $rs->totalPrev01;
		$totalPrevMonth02 = $totalPrevMonth01 + $rs->totalPrev02;
		$totalPrevMonth03 = $totalPrevMonth02 + $rs->totalPrev03;
		$totalPrevMonth04 = $totalPrevMonth03 + $rs->totalPrev04;
		$totalPrevMonth05 = $totalPrevMonth04 + $rs->totalPrev05;
		$totalPrevMonth06 = $totalPrevMonth05 + $rs->totalPrev06;
		$totalPrevMonth07 = $totalPrevMonth06 + $rs->totaPrevl07;
		$totalPrevMonth08 = $totalPrevMonth07 + $rs->totalPrev08;
		$totalPrevMonth09 = $totalPrevMonth08 + $rs->totalPrev09;

		$tables->rows[]=array(
				++$i,
				$rs->label,
				__project_fund_report_expensebymonth_million($totalMonth10)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth10, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth11)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth11, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth12)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth12, '-prev'),

				__project_fund_report_expensebymonth_million($totalMonth01)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth01, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth02)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth02, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth03)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth03, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth04)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth04, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth05)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth05, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth06)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth06, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth07)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth07, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth08)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth08, '-prev'),
				__project_fund_report_expensebymonth_million($totalMonth09)
				.'<br />'.__project_fund_report_expensebymonth_million($totalPrevMonth09, '-prev'),
				);

		$chartTable->rows['ต.ค.']['string:Year']='ต.ค.'.$yearTo1;
		$chartTable->rows['ต.ค.']['number:'.$rs->label]=$totalMonth10;
		$chartTable->rows['ต.ค.']['string:'.$rs->label.':role']='';

		$chartTable->rows['พ.ย.']['string:Year']='พ.ย.'.$yearTo1;
		$chartTable->rows['พ.ย.']['number:'.$rs->label]=$totalMonth11;
		$chartTable->rows['พ.ย.']['string:'.$rs->label.':role']='';

		$chartTable->rows['ธ.ค.']['string:Year']='ธ.ค.'.$yearTo1;
		$chartTable->rows['ธ.ค.']['number:'.$rs->label]=$totalMonth12;
		$chartTable->rows['ธ.ค.']['string:'.$rs->label.':role']='';

		$chartTable->rows['ม.ค.']['string:Year']='ม.ค.'.$yearTo2;
		$chartTable->rows['ม.ค.']['number:'.$rs->label]=$totalMonth01;
		$chartTable->rows['ม.ค.']['string:'.$rs->label.':role']='';

		$chartTable->rows['ก.พ.']['string:Year']='ก.พ.'.$yearTo2;
		$chartTable->rows['ก.พ.']['number:'.$rs->label]=$totalMonth02;
		$chartTable->rows['ก.พ.']['string:'.$rs->label.':role']='';

		$chartTable->rows['มี.ค.']['string:Year']='มี.ค.'.$yearTo2;
		$chartTable->rows['มี.ค.']['number:'.$rs->label]=$totalMonth03;
		$chartTable->rows['มี.ค.']['string:'.$rs->label.':role']='';

		$chartTable->rows['เม.ย.']['string:Year']='เม.ย.'.$yearTo2;
		$chartTable->rows['เม.ย.']['number:'.$rs->label]=$totalMonth04;
		$chartTable->rows['เม.ย.']['string:'.$rs->label.':role']='';

		$chartTable->rows['พ.ค.']['string:Year']='พ.ค.'.$yearTo2;
		$chartTable->rows['พ.ค.']['number:'.$rs->label]=$totalMonth05;
		$chartTable->rows['พ.ค.']['string:'.$rs->label.':role']='';

		$chartTable->rows['มิ.ย.']['string:Year']='มิ.ย.'.$yearTo2;
		$chartTable->rows['มิ.ย.']['number:'.$rs->label]=$totalMonth06;
		$chartTable->rows['มิ.ย.']['string:'.$rs->label.':role']='';

		$chartTable->rows['ก.ค.']['string:Year']='ก.ค.'.$yearTo2;
		$chartTable->rows['ก.ค.']['number:'.$rs->label]=$totalMonth07;
		$chartTable->rows['ก.ค.']['string:'.$rs->label.':role']='';

		$chartTable->rows['ส.ค.']['string:Year']='ส.ค.'.$yearTo2;
		$chartTable->rows['ส.ค.']['number:'.$rs->label]=$totalMonth08;
		$chartTable->rows['ส.ค.']['string:'.$rs->label.':role']='';

		$chartTable->rows['ก.ย.']['string:Year']='ก.ย.'.$yearTo2;
		$chartTable->rows['ก.ย.']['number:'.$rs->label]=$totalMonth09;
		$chartTable->rows['ก.ย.']['string:'.$rs->label.':role']='';


	}
	$tables->tfoot[]=array(
											'<td></td>',
											'รวม',
											__project_fund_report_expensebymonth_million($sumTotalMonth10),
											__project_fund_report_expensebymonth_million($sumTotalMonth11),
											__project_fund_report_expensebymonth_million($sumTotalMonth12),
											__project_fund_report_expensebymonth_million($sumTotalMonth01),
											__project_fund_report_expensebymonth_million($sumTotalMonth02),
											__project_fund_report_expensebymonth_million($sumTotalMonth03),
											__project_fund_report_expensebymonth_million($sumTotalMonth04),
											__project_fund_report_expensebymonth_million($sumTotalMonth05),
											__project_fund_report_expensebymonth_million($sumTotalMonth06),
											__project_fund_report_expensebymonth_million($sumTotalMonth07),
											__project_fund_report_expensebymonth_million($sumTotalMonth08),
											__project_fund_report_expensebymonth_million($sumTotalMonth09),
											);

	if ($graphSummary) {
		unset($chartTable->rows);
		$chartTable->rows['ต.ค.']['string:Year']='ต.ค.'.$yearTo1;
		$chartTable->rows['ต.ค.']['number:การใช้จ่าย']=$sumTotalMonth10;
		$chartTable->rows['ต.ค.']['string:'.':role']=number_format($sumTotalMonth10/1000000,2).'M';

		$chartTable->rows['พ.ย.']['string:Year']='พ.ย.'.$yearTo1;
		$chartTable->rows['พ.ย.']['number:การใช้จ่าย']=$sumTotalMonth11;
		$chartTable->rows['พ.ย.']['string:'.':role']=number_format($sumTotalMonth11/1000000,2).'M';

		$chartTable->rows['ธ.ค.']['string:Year']='ธ.ค.'.$yearTo1;
		$chartTable->rows['ธ.ค.']['number:การใช้จ่าย']=$sumTotalMonth12;
		$chartTable->rows['ธ.ค.']['string:'.':role']=number_format($sumTotalMonth12/1000000,2).'M';

		$chartTable->rows['ม.ค.']['string:Year']='ม.ค.'.$yearTo2;
		$chartTable->rows['ม.ค.']['number:การใช้จ่าย']=$sumTotalMonth01;
		$chartTable->rows['ม.ค.']['string:'.':role']=number_format($sumTotalMonth01/1000000,2).'M';

		$chartTable->rows['ก.พ.']['string:Year']='ก.พ.'.$yearTo2;
		$chartTable->rows['ก.พ.']['number:การใช้จ่าย']=$sumTotalMonth02;
		$chartTable->rows['ก.พ.']['string:'.':role']=number_format($sumTotalMonth02/1000000,2).'M';

		$chartTable->rows['มี.ค.']['string:Year']='มี.ค.'.$yearTo2;
		$chartTable->rows['มี.ค.']['number:การใช้จ่าย']=$sumTotalMonth03;
		$chartTable->rows['มี.ค.']['string:'.':role']=number_format($sumTotalMonth03/1000000,2).'M';

		$chartTable->rows['เม.ย.']['string:Year']='เม.ย.'.$yearTo2;
		$chartTable->rows['เม.ย.']['number:การใช้จ่าย']=$sumTotalMonth04;
		$chartTable->rows['เม.ย.']['string:'.':role']=number_format($sumTotalMonth04/1000000,2).'M';

		$chartTable->rows['พ.ค.']['string:Year']='พ.ค.'.$yearTo2;
		$chartTable->rows['พ.ค.']['number:การใช้จ่าย']=$sumTotalMonth05;
		$chartTable->rows['พ.ค.']['string:'.':role']=number_format($sumTotalMonth05/1000000,2).'M';

		$chartTable->rows['มิ.ย.']['string:Year']='มิ.ย.'.$yearTo2;
		$chartTable->rows['มิ.ย.']['number:การใช้จ่าย']=$sumTotalMonth06;
		$chartTable->rows['มิ.ย.']['string:'.':role']=number_format($sumTotalMonth06/1000000,2).'M';

		$chartTable->rows['ก.ค.']['string:Year']='ก.ค.'.$yearTo2;
		$chartTable->rows['ก.ค.']['number:การใช้จ่าย']=$sumTotalMonth07;
		$chartTable->rows['ก.ค.']['string:'.':role']=number_format($sumTotalMonth07/1000000,2).'M';

		$chartTable->rows['ส.ค.']['string:Year']='ส.ค.'.$yearTo2;
		$chartTable->rows['ส.ค.']['number:การใช้จ่าย']=$sumTotalMonth08;
		$chartTable->rows['ส.ค.']['string:'.':role']=number_format($sumTotalMonth08/1000000,2).'M';

		$chartTable->rows['ก.ย.']['string:Year']='ก.ย.'.$yearTo2;
		$chartTable->rows['ก.ย.']['number:การใช้จ่าย']=$sumTotalMonth09;
		$chartTable->rows['ก.ย.']['string:'.':role']=number_format($sumTotalMonth09/1000000,2).'M';
	}

	$options=array(
							"legend"=>array("position"=>"bottom"),
							"hAxis"=>array(
													"textStyle"=>array(
															"fontSize"=>12,
															)
													),
						);
	$ret.='<div id="fund-join" class="sg-chart -join" data-chart-type="col" data-options=\''.json_encode($options).'\'><h3>แผนภูมิแสดงการใช้จ่ายของกองทุนฯ รายเดือน</h3>'.$chartTable->build().'</div>'._NL;
	//$ret.=$chartTable->build();
	//$ret.=print_o($chartTable,'$chartTable');

	$ret.=$tables->build();
	//$ret.='<p>หมายเหตุ: คลิกที่ชื่อเขตเพื่อดูรายละเอียด</p>';

	//$ret.=print_o($dbs,'$dbs');

	head('googlegraph','<script type="text/javascript" src="https
		://www.gstatic.com/charts/loader.js"></script>');
	$ret.='<style type="text/css">
	.sg-chart {height:400px; overflow:hidden;}
	.money.-prev {color:#bbb;}
	</style>';

	$ret.='<script type="text/javascript">
	$("body").on("change","#condition select,#condition input", function() {
		var $this=$(this);
		if ($this.attr("name")=="area") {
			$("#province").val("");
			$("#ampur").val("");
		}
		if ($this.attr("name")=="prov") {
			$("#ampur").val("");
		}
		notify("กำลังโหลด");
		console.log($(this).attr("name"))
		$(this).closest("form").submit();
	});
	</script>';

	return $ret;
}

function __project_fund_report_expensebymonth_million($number, $class = NULL) {
	$ret = '<span class="money '.($class).'" title="'.number_format($number,2).' บาท">'.number_format($number/1000000,3).' ลบ.</span>';
	return $ret;
}
?>