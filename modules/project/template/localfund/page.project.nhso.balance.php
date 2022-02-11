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

function project_nhso_balance($self) {
	$getYear = intval(SG\getFirst(post('yy'), date('Y')));
	$getMonth = intval(post('mm'));
	$getArea = post('area');
	$getChangwat = post('prov');
	$getAmpur = post('ampur');
	$getOrg = post('org');
	$graphSummary  =post('gr') == 1;
	$formatOutput = post('fmt');

	$ret = '';

	$isAdmin = is_admin() || post('token') == 'ASDWE9283dEkfkdieDKFLKDK';

	if (!$isAdmin) return message('error', 'Access Denied');

	// ปีงบประมาณ 2562 => 2561-10 to 2562-09 => 2018 => 2018-10 - 2019-09
	// ปีงบประมาณ 2563 => 2562-10 to 2563-09 => 2019 => 2019-10 - 2020-09

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

	$repTitle = 'สปสช. - ข้อมูลการเงิน (DRAFT)';


	R::View('project.toolbar',$self, $repTitle, 'fund');


	$form = new Form(NULL, url('project/nhso/balance'), NULL, '-inlineitem');
	$form->addConfig('method', 'GET');

	// Select year
	$stmt = 'SELECT DISTINCT YEAR(`refdate`)+IF(MONTH(`refdate`) >= 10, 1, 0) `budgetYear` FROM %project_gl% HAVING `budgetYear` ORDER BY `budgetYear` ASC';
	$getYearSelectOption = array();
	foreach (mydb::select($stmt)->items as $rs) {
		$getYearSelectOption[$rs->budgetYear] = 'พ.ศ.'.($rs->budgetYear+543);
	}

	$form->addField(
		'yy',
		array(
			'type' => 'select',
			'value' => $getYear,
			'options' => $getYearSelectOption,
		)
	);

	$getMonthSelectOption = array(
		'' => 'ทั้งปีงบประมาณ',
		'10' => 'ตุลาคม',
		'11' => 'พฤศจิกายน',
		'12' => 'ธันวาคม',
		'01' => 'มกราคม',
		'02' => 'กุมภาพันธ์',
		'03' => 'มีนาคม',
		'04' => 'เมษายน',
		'05' => 'พฤษภาคม',
		'06' => 'มิถุนายน',
		'07' => 'กรกฏาคม',
		'08' => 'สิงหาคม',
		'09' => 'กันยายน',
	);

	$form->addField(
		'mm',
		array(
			'type' => 'select',
			'value' => $getMonth,
			'options' => $getMonthSelectOption,
		)
	);

	// Select area
	$areaSelectOption = array('' => 'ทุกเขต');
	$stmt = 'SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype` = "nhso" ORDER BY `areaid`+0 ASC';
	foreach (mydb::select($stmt)->items as $rs) {
		$areaSelectOption[$rs->areaid] = 'เขต '.$rs->areaid.' '.$rs->areaname;
	}

	$form->addField(
		'area',
		array(
			'type' => 'select',
			'value' => $getArea,
			'options' => $areaSelectOption,
		)
	);

	// Select province
	if ($getArea) {
		$changwatSelectOption = array('' => 'ทุกจังหวัด');

		$stmt = 'SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid` = :areaid HAVING `provname` IS NOT NULL';
		foreach (mydb::select($stmt,':areaid',$getArea)->items as $rs) {
			$changwatSelectOption[$rs->changwat] = $rs->provname;
		}
		$form->addField(
			'prov',
			array(
				'type' => 'select',
				'value' => $getChangwat,
				'options' => $changwatSelectOption,
			)
		);
	}

	// Select province
	/*
	if ($getChangwat) {
		$stmt = 'SELECT DISTINCT `distid`, `distname` FROM  %co_district% WHERE LEFT(`distid`,2) = :prov HAVING `distname` IS NOT NULL ';
		$ampurList = mydb::select($stmt,':prov',$getChangwat);
		$form .= '<select id="ampur" class="form-select" name="ampur">';
		$form .= '<option value="">ทุกอำเภอ</option>';
		foreach ($ampurList->items as $rs) {
			$form .= '<option value="'.substr($rs->distid,2).'" '.(substr($rs->distid,2)==$getAmpur?'selected="selected"':'').'>'.$rs->distname.'</option>';
		}
		$form .= '</select> ';
	}
	*/

	$form->addText('<button class="btn -primary" type="submit"><i class="icon -search -white"></i></button> <button class="btn -secondary" type="submit" name="fmt" value="excel"><i class="icon -download"></i><span>EXCEL</span></button> <button class="btn -secondary" type="submit" name="fmt" value="rest"><i class="icon -download"></i><span>REST</span></button> <button class="btn -secondary" type="submit" name="fmt" value="sql"><i class="icon -download"></i><span>SQL</span></button>');

	$ret .= '<nav class="nav -page">'.$form->build().'</nav>';


	if (empty($getYear)) return $ret . message('notify', 'กรุณาเลือกปีงบประมาณ');


	mydb::where('f.`fundid` NOT IN ("L0000")');
	//mydb::where('gl.`refdate` BETWEEN :startdate AND :enddate AND gc.`gltype` IN (4,5)', ':startdate ', $startDate, ':enddate', $endDate);
	if ($getOrg) mydb::where('f.`orgid` = :orgid', ':orgid', $getOrg);
	else if ($getAmpur) mydb::where('f.`ampur` = :ampur',':ampur',$getAmpur);
	else if ($getChangwat) mydb::where('f.`changwat` = :prov',':prov',$getChangwat);
	else if ($getArea) mydb::where('f.`areaid` = :areaid',':areaid',$getArea);


	// Start report query

	mydb::where(NULL, ':startdate', $startDate, ':enddate', $endDate, ':closebalancedate', $closeDate);

	/*
	if ($getArea) {
	 mydb::where('f.`areaid`=:areaid',':areaid',$getArea);
	 $label='f.`namechangwat`';
	}
	if ($getAmpur) {
		mydb::where('f.`changwat`=:prov AND f.`ampur`=:ampur',':prov',$getChangwat,':ampur',$getAmpur);
		$label='f.`fundname`';
	} else if ($getChangwat) {
		mydb::where('f.`changwat`=:prov',':prov',$getChangwat);
		$label='f.`nameampur`';
	}
	*/

	$stmt = 'SELECT
			  f.`fundname` `label`
			,	f.*
			, f.`openbalance` - f.`afterOpen` `periodOpenBalance`
			FROM
			(
				SELECT
					  f.`orgid`
					, f.`areaid`
					, f.`changwat`
					, f.`ampur`
					, f.`namechangwat`
					, f.`nameampur`
					, o.`shortname` `fundid`
					, o.`name` `fundname`
					, f.`openbalance`
					, SUM(IF(gc.`gltype` IN (4,5) AND gl.`refdate` BETWEEN f.`openbaldate` AND :closebalancedate,gl.`amount`,0)) `afterOpen`
					, ABS(SUM(IF(gc.`gltype` = 4 AND gl.`refdate` BETWEEN :startdate AND :enddate,gl.`amount`,0))) `totalRevenue`
					, ABS(SUM(IF(gc.`gltype` = 5 AND gl.`refdate` BETWEEN :startdate AND :enddate,gl.`amount`,0))) `totalExpend`
				FROM %project_fund% f
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_gl% gl ON gl.`orgid` = f.`orgid`
					LEFT JOIN %glcode% gc USING(`glcode`)
				%WHERE%
				GROUP BY `orgid`
			) f
			ORDER BY CONVERT(`label` USING tis620) ASC
			;
			-- {sum:"periodOpenBalance,totalRevenue,totalExpend"}
			';

	/*
	$stmt = 'SELECT
					  f.`orgid`
					, f.`areaid`
					, f.`fundid`
					, f.`changwat`
					, f.`ampur`
					, f.`namechangwat`
					, f.`nameampur`
					, o.`name` `fundname`
					, o.`shortname`
					, gl.`refdate`
					, MONTH(gl.`refdate`) `refmonth`
					, gl.`glcode`
					, CASE
							WHEN `glcode` = "40100" THEN 1
							WHEN `glcode` = "40200" THEN 2
							WHEN `glcode` = "40300" THEN 3
							WHEN `glcode` = "40400" THEN 4
							WHEN `glcode` = "40500" THEN 61
							WHEN `glcode` = "50100" THEN 5
							WHEN `glcode` = "50200" THEN 6
							WHEN `glcode` = "50300" THEN 7
							WHEN `glcode` = "50400" THEN 8
							WHEN `glcode` = "50500" THEN 9
						END `nhsoglcode`
					, gc.`glname`
					, ABS(gl.`amount`) `amount`
				FROM %project_fund% f
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_gl% gl ON gl.`orgid` = f.`orgid`
					LEFT JOIN %glcode% gc USING(`glcode`)
				%WHERE%
				ORDER BY `refdate` ASC
				';

	*/

	$dbs = mydb::select($stmt);

	//$ret .= '<pre>'.mydb()->_query.'</pre>';

	$tables = new Table();
	$tables->thead = array(
		'รหัสกองทุน',
		'year -amt' => 'ปีงบประมาณ',
		'refmonth -date' => 'เดือน',
		'openbal -money' => 'ยอดเงินคงเหลือยกมา',
		'revenue -money' => 'รายรับ',
		'expend -money' => 'รายจ่าย',
	);

	$exports->numrows = $dbs->count();
	foreach ($dbs->items as $rs) {
		$rs->amount = (float) $rs->amount;
		$rs->fundid = (string) $rs->fundid;

		$tables->rows[] = array(
			$rs->fundid.' - '.$rs->fundname,
			$getYear + 543,
			$getMonth ? sg_date($startDate, 'ดดด ปปปป') : '',
			number_format($rs->periodOpenBalance, 2),
			number_format($rs->totalRevenue, 2),
			number_format($rs->totalExpend,2)
		);

		$reportId = (String) ($getYear*10000000 + $getMonth*100000 + $rs->orgid);
		$exports->rows[] = (Object) array(
			'reportId' => $reportId,
			'fundCode' => $rs->fundid,
			'orgid' => $rs->orgid,
			'budgetYear' => $getYear,
			'monthId' => $getMonth,
			'balanceForward' => $rs->periodOpenBalance,
			'Revenue' => $rs->totalRevenue,
			'Expenditure' => $rs->totalExpend
		);
		$excels->rows[] = array(
			'fundCode' => $rs->fundid,
			'budgetYear' => $getYear,
			'monthId' => $getMonth,
			'balanceForward' => $rs->periodOpenBalance,
			'Revenue' => $rs->totalRevenue,
			'Expenditure' => $rs->totalExpend
		);
	}

	$tables->tfoot[] = array(
		'รวม',
		'',
		'',
		number_format($dbs->sum->periodOpenBalance,2),
		number_format($dbs->sum->totalRevenue,2),
		number_format($dbs->sum->totalExpend,2),
	);

	$ret .= $tables->build();

	//$ret .= print_o($dbs, '$dbs');

	if ($formatOutput == 'sql') {
		$exportSql = '';
		$reportId = 0;
		foreach ($exports->rows as $value) {
			$reportId = (String) ($value->budgetYear*10000000 + $value->monthId*100000 + $value->orgid);
			$value->reportid = $reportId;

			/*
			$exportSql .= "INSERT INTO OBT62_BALANCE_REPORT (REPORT_ID, FUND_CODE, BUDGET_YEAR, MONTH_ID, BALANCE_FORWARD, REVENUE, EXPENDITURE) VALUES ({$value->reportid}, '{$value->fundCode}', {$value->budgetYear}, {$value->monthId}, {$value->balanceForward}, {$value->Revenue}, {$value->Expenditure});"._NL;
			*/

			$exportSql .= "BEGIN
	INSERT INTO OBT62_BALANCE_REPORT (REPORT_ID, FUND_CODE, BUDGET_YEAR, MONTH_ID, BALANCE_FORWARD, REVENUE, EXPENDITURE) VALUES ({$value->reportid}, '{$value->fundCode}', {$value->budgetYear}, {$value->monthId}, {$value->balanceForward}, {$value->Revenue}, {$value->Expenditure});
	EXCEPTION
		WHEN DUP_VAL_ON_INDEX THEN
			UPDATE OBT62_BALANCE_REPORT
				SET BALANCE_FORWARD = {$value->balanceForward}, REVENUE = {$value->Revenue}, EXPENDITURE = {$value->Expenditure}
			WHERE REPORT_ID = $reportId;

END;"._NL._NL;

			/*
			begin
			INSERT INTO users VALUES(1,10);

			exception
				when dup_val_on_index then
					update users
						set points = 10
				where id = 1;
			end;
			*/
		}
		die('<textarea class="form-textarea -fill" style="height:100%; width:100%">'.$exportSql.'</textarea>');
	} else if ($formatOutput == 'rest') {
		die(json_encode($exports));
	} else if ($formatOutput == 'excel') {
		$excels->thead = $tables->thead;
		die(R::Model('excel.export',$excels,'NHSO-MONEY-'.$getYear.($getChangwat ? '-'.$getChangwat : '').' '.date('Y-m-d-H-i-s').'.xls','{debug:false}'));
	}


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
?>