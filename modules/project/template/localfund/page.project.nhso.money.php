<?php
/**
* Project NHSO Money for Export
*
* @param Object $self
* @return String
*/

$debug = true;

function project_nhso_money($self) {
	$year = SG\getFirst(post('yr'), date('Y'));
	$area = post('area');
	$prov = post('prov');
	$ampur = post('ampur');
	$graphSummary  =post('gr') == 1;

	$startDate = ($year-1).'-10-01';
	$endDate = $year.'-09-30';

	$repTitle='สปสช. - ข้อมูลการเงิน (DRAFT)';


	R::View('project.toolbar',$self, $repTitle, 'fund');

	$ret = '';

	$form = '<form id="condition" action="'.url('project/nhso/money').'" method="get">';
	$form .= '<span>ตัวเลือก </span>';
	
	// Select year
	$stmt = 'SELECT DISTINCT YEAR(`refdate`)+IF(MONTH(`refdate`) >= 10, 1, 0) `budgetYear` FROM %project_gl% HAVING `budgetYear` ORDER BY `budgetYear` ASC';
	$yearList = mydb::select($stmt);
	$form .= '<select id="year" class="form-select" name="yr">';
	//$form.='<option value="">ทุกปีงบประมาณ</option>';
	foreach ($yearList->items as $rs) {
		$form .= '<option value="'.$rs->budgetYear.'" '.($rs->budgetYear==$year?'selected="selected"':'').'>พ.ศ.'.($rs->budgetYear+543).'</option>';
	}
	$form .= '</select> ';

	// Select area
	$form .= '<select id="area" class="form-select" name="area">';
	$form .= '<option value="">ทุกเขต</option>';
	$areaList = mydb::select('SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype` = "nhso" ORDER BY `areaid`+0 ASC');
	foreach ($areaList->items as $rs) {
		$form .= '<option value="'.$rs->areaid.'" '.($rs->areaid==$area?'selected="selected"':'').'>เขต '.$rs->areaid.' '.$rs->areaname.'</option>';
	}
	$form .= '</select> ';

	// Select province
	if ($area) {
		$stmt = 'SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid` = :areaid HAVING `provname` IS NOT NULL';
		$provList = mydb::select($stmt,':areaid',$area);
		$form .= '<select id="province" class="form-select" name="prov">';
		$form .= '<option value="">ทุกจังหวัด</option>';
		foreach ($provList->items as $rs) {
			$form .= '<option value="'.$rs->changwat.'" '.($rs->changwat==$prov?'selected="selected"':'').'>'.$rs->provname.'</option>';
		}
		$form .= '</select> ';
	}

	// Select province
	if ($prov) {
		$stmt = 'SELECT DISTINCT `distid`, `distname` FROM  %co_district% WHERE LEFT(`distid`,2) = :prov HAVING `distname` IS NOT NULL ';
		$ampurList = mydb::select($stmt,':prov',$prov);
		$form .= '<select id="ampur" class="form-select" name="ampur">';
		$form .= '<option value="">ทุกอำเภอ</option>';
		foreach ($ampurList->items as $rs) {
			$form .= '<option value="'.substr($rs->distid,2).'" '.(substr($rs->distid,2)==$ampur?'selected="selected"':'').'>'.$rs->distname.'</option>';
		}
		$form .= '</select> ';
	}
	//$form.='<label><input type="checkbox" name="gr" value="1" '.($graphSummary==1?'checked="checked"':'').' />แสดงกราฟผลรวม</label>';

	$form .= '<button class="btn -primary" type="submit"><i class="icon -search -white"></i></button> ';
	$form .= '<button class="btn -secondary" type="submit" name="fmt" value="excel"><i class="icon -download"></i><span>Export</span></button>';
	$form .= '</form>'._NL;

	$ret .= '<nav class="nav -page">'.$form.'</nav>';


	if (empty($year)) return $ret . message('notify', 'กรุณาเลือกปีงบประมาณ');


	mydb::where('o.`shortname` NOT IN ("L0000")');
	mydb::where('gl.`refdate` BETWEEN :startdate AND :enddate AND gc.`gltype` IN (4,5)', ':startdate ', $startDate, ':enddate', $endDate);
	if ($area) mydb::where('f.`areaid`=:areaid',':areaid',$area);
	if ($prov) mydb::where('f.`changwat`=:prov',':prov',$prov);
	if ($ampur) mydb::where('f.`ampur`=:ampur',':ampur',$ampur);

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
	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('รหัสกองทุน', 'refmonth -date' => 'เดือน', 'year -amt' => 'ปีงบประมาณ (ค.ศ.)', 'รายการ', 'amt money' => 'จำนวนเงิน');

	$exports->numrows = $dbs->count();
	foreach ($dbs->items as $rs) {
		$rs->amount = (float) $rs->amount;
		$rs->fundid = (string) $rs->fundid;
		$tables->rows[] = array(
												$rs->fundid.' - '.$rs->fundname,
												$rs->refmonth.' - '.sg_date($rs->refdate, 'ดดด'),
												$year,
												$rs->nhsoglcode.' - '.$rs->glname,
												number_format($rs->amount,2)
											);
		$exports->rows[] = array(
								$rs->fundid,
								$rs->refmonth,
								$year,
								$rs->nhsoglcode,
								$rs->amount
							);
	}
	$ret .= $tables->build();

	//$ret .= print_o($dbs, '$dbs');

	$formatOutput = post('fmt');
	if ($formatOutput == 'rest') {
		die(json_encode($exports));
	} else if ($formatOutput == 'excel') {
		$exports->thead = $tables->thead;
		die(R::Model('excel.export',$exports,'NHSO-MONEY-'.$year.($prov ? '-'.$prov : '').' '.date('Y-m-d-H-i-s').'.xls','{debug:false}'));
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