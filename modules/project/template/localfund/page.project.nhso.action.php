<?php
/**
* Project NHSO Action for Export
*
* @param Object $self
* @return String
*/

$debug = true;

function project_nhso_action($self) {
	$year = SG\getFirst(post('yr'), date('Y'));
	$area = post('area');
	$prov = post('prov');
	$ampur = post('ampur');
	$graphSummary  =post('gr') == 1;
	$formatOutput = post('fmt');

	$startDate = ($year-1).'-10-01';
	$endDate = $year.'-09-30';

	$repTitle='สปสช. - ข้อมูลกิจกรรม (DRAFT)';


	R::View('project.toolbar',$self, $repTitle, 'fund');

	$ret = '';

	$form = '<form id="condition" action="'.url('project/nhso/action').'" method="get">';
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
		notify("LOADING");
		console.log($(this).attr("name"))
		$(this).closest("form").submit();
	});
	</script>';


	if (empty($year) || empty($area) || empty($prov))
		return $ret . message('notify', 'กรุณาเลือก ปีงบประมาณ เขต และ จังหวัด');


	mydb::where('p.`prtype` = "โครงการ" AND o.`shortname` NOT IN ("L0000")');
	mydb::where('p.`pryear` = :pryear', ':pryear', $year);
	if ($area) mydb::where('f.`areaid`=:areaid',':areaid',$area);
	if ($prov) mydb::where('f.`changwat`=:prov',':prov',$prov);
	if ($ampur) mydb::where('f.`ampur`=:ampur',':ampur',$ampur);

	$stmt = 'SELECT
					  t.`orgid`
					, f.`areaid`
					, f.`fundid`
					, f.`changwat`
					, f.`ampur`
					, f.`namechangwat`
					, f.`nameampur`
					, o.`name` `fundname`
					, o.`shortname`
					, p.`tpid`
					, t.`title`
					, p.`pryear`
					, p.`date_from`
					, p.`date_end`
					, p.`date_approve`
					, p.`budget`
					, p.`supporttype`
					, p.`orgnamedo`
					, spt.`name` `supporttypeName`
					, p.`supportorg`
					, spo.`name` `supportorgName`
					, (SELECT `tgtid` FROM %project_target% tgt WHERE tgt.`tpid` = p.`tpid` LIMIT 1) `targetGroup`
					, (SELECT SUM(`amount`) FROM %project_target% tgt WHERE tgt.`tpid` = p.`tpid` LIMIT 1) `targetAmount`
					, (SELECT
							GROUP_CONCAT(DISTINCT mtp.`nhsocode`)
							-- GROUP_CONCAT(mp.`tgtid`,":",mp.`parent`,":",mtp.`nhsocode`)
							FROM %topic_parent% mp
								LEFT JOIN %project_targetplan% mtp ON mtp.`tpid`=mp.`parent` AND mtp.`tgtid`=mp.`tgtid`
							WHERE mp.`tpid` = p.`tpid`) `mainactivity`
					, (SELECT SUM(`amount`) FROM %project_paiddoc% mpd WHERE mpd.`tpid`=p.`tpid`) `paidamount`
					, (SELECT SUM(`num1`) FROM %project_tr% mpb WHERE mpb.`tpid`=p.`tpid` AND `formid`="info" AND `part`="moneyback") `moneybackamount`
					, CASE
						WHEN p.`performance`=1 THEN 1
						WHEN p.`performance`=0 THEN 2
						END `performance`
					-- , p.`jointarget`
					, (SELECT GROUP_CONCAT(`text1` SEPARATOR "<br />") FROM %project_tr% mpo WHERE mpo.`formid` = "info" AND `part` = "objective" AND `tpid` = p.`tpid`) `objective`
				FROM %project% p
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_fund% f USING(`orgid`)
					LEFT JOIN %tag% spt ON spt.`taggroup` = "project:supporttype" AND spt.`catid` = p.`supporttype`
					LEFT JOIN %tag% spo ON spo.`taggroup` = "project:supportorg" AND spo.`catid` = p.`supportorg`
				%WHERE%
				HAVING `fundid` IS NOT NULL
				ORDER BY `date_approve` ASC
				';
	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;


	$targetConvert = array(1001=>2, 1002=>3, 1004=>5, 2001=>1, 2002=>6, 2003=>7, 2004=>8, 2005=>9);

	$tables = new Table();
	$tables->thead = array(
										'fundid -nowrap' => 'รหัสกองทุน',
										'title -nowrap' => 'ชื่อแผนงาน/โครงการ/กิจกรรม',
										'type -nowrap' => 'ประเภทแผนงาน/โครงการ/กิจกรรม',
										'org -nowrap' => 'องค์กร/หน่วยงานที่รับผิดชอบ',
										'orgname -nowrap' => 'องค์กร/หน่วยงานที่รับผิดชอบอื่นๆ',
										'กลุ่มเป้าหมาย',
										'targetamt -amt' => 'จำนวน',
										'obj -nowrap' => 'วัตถุประสงค์ที่สำคัญ',
										'กิจกรรมหลักของโครงการ',
										'กิจกรรมหลักของโครงการอื่นๆ',
										'budget -money' => 'งบประมาณที่อนุมัติ',
										'budgetyear -date' => 'ปีงบประมาณ (ค.ศ.)',
										'รายละเอียดงบประมาณ',
										'from -date' => 'วันที่เริ่มต้น (วว/ดด/ปปปป)',
										'end -date' => 'วันที่สิ้นสุด (วว/ดด/ปปปป)',
										'approve -date' => 'วันที่ได้รับอนุมัติ (วว/ดด/ปปปป)',
										'paidamount -money' => 'งบประมาณที่ใช้ไป',
										'performance -amt' => 'ผลการดำเนินโครงการ/กิจกรรม',
										'เหตุผลที่ไม่บรรลุวัตถุประสงค์',
										'jointarget -amt' => 'จำนวนผู้เข้าร่วมในแผนงาน/โครงการ/กิจกรรม'
									);

	$exports->numrows = $dbs->count();
	foreach ($dbs->items as $rs) {
		$rs->budget = (float) $rs->budget;
		$rs->fundid = (string) $rs->fundid;
		$rs->date_from = $rs->date_from ? sg_date($rs->date_from, 'd/m/Y') : '';
		$rs->date_end = $rs->date_end ? sg_date($rs->date_end, 'd/m/Y') : '';
		$rs->date_approve = $rs->date_approve ? sg_date($rs->date_approve, 'd/m/Y') : '';

		$tables->rows[] = array(
												$rs->fundid.' - '.$rs->fundname,
												/*$rs->tpid.' - '.*/$rs->title,
												$rs->supporttypeName,
												$rs->supportorgName,
												$rs->orgnamedo,
												$targetConvert[$rs->targetGroup],
												$rs->targetAmount,
												$rs->objective,
												$rs->mainactivity,
												'',
												number_format($rs->budget,2),
												$rs->pryear,
												'',
												$rs->date_from,
												$rs->date_end,
												$rs->date_approve,
												number_format($rs->paidamount - $rs->moneybackamount,2),
												$rs->performance,
												'',
												$rs->jointarget ? number_format($rs->jointarget) : '',
											);
		$exports->rows[] = array(
												$rs->fundid,
												$rs->title,
												$rs->supporttype,
												$rs->supportorg,
												$rs->orgnamedo,
												$targetConvert[$rs->targetGroup],
												$rs->targetAmount,
												$rs->objective,
												$rs->mainactivity,
												'',
												$rs->budget,
												$rs->pryear,
												'',
												$rs->date_from,
												$rs->date_end,
												$rs->date_approve,
												$rs->paidamount - $rs->moneybackamount,
												$rs->performance,
												'',
												$rs->jointarget ? $rs->jointarget : NULL,
							);
	}
	$ret .= '<div style="width:100%; overflow:scroll;">'.$tables->build().'</div>';

	//$ret .= print_o($dbs, '$dbs');

	if ($formatOutput == 'rest') {
		die(json_encode($exports));
	} else if ($formatOutput == 'excel') {
		$exports->thead = $tables->thead;
		die(R::Model('excel.export',$exports,'NHSO-ACTION-'.$year.($prov ? '-'.$prov : '').' '.date('Y-m-d-H-i-s').'.xls','{debug:false}'));
	}


	return $ret;
}
?>