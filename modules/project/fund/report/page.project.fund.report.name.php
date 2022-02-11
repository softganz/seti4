<?php
/**
* Project :: Fund Report Project By Name
* Created 2018-02-23
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/name
*/

$debug = true;

function project_fund_report_name($self) {
	project_model::set_toolbar($self, 'รายงานรายชื่อโครงการ');

	$planid = post('planid');
	$prov = post('prov');
	$ampur = post('ampur');
	$year = SG\getFirst(post('year') , date('Y'));
	$fundid = post('fund');
	$searchText = post('stext');
	$status = post('status');
	$export = post('export');

	$yearList = mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->lists->text;

	$ret .= '<nav class="nav -page">';
	$ret .= '<form id="project-develop" class="form-report" method="get" action="'.url('project/fund/report/name').'">';
	$ret .= '<ul>';

	$ret .= '<li class="ui-nav">';
	// Select year
	if (strpos($yearList, ',')) {
		$ret .= '<select class="form-select" name="year" id="input-year"><option value="">==ทุกปี==</option>';
		foreach (explode(',',$yearList) as $item) {
			$ret .= '<option value="'.$item.'" '.($item == $year ? 'selected="selected"' : '').'>พ.ศ. '.($item+543).'</option>';
		}
		$ret .= '</select>';
	} else {
		$ret .= '<input type="hidden" name="year" value="'.$yearList.'" />';
	}

	// Select project set
	$planList = model::get_category('project:planning', 'catid');
	$ret .= '<select class="form-select" name="planid" id="input-planid"><option value="">==ทุกแผนงาน==</option>';
	foreach ($planList as $catid => $catname) {
		$ret .= '<option value="'.$catid.'" '.($catid == $planid ? 'selected="selected"' : '').'>'.$catname.'</option>';
	}
	$ret .= '</select>';

	// Select province
	$ret .= '<select class="form-select" name="prov" id="input-changwat"><option value="">==ทุกจังหวัด==</option>';
	$provDb = mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %topic% t LEFT JOIN %co_province% cop ON cop.`provid` = t.`changwat` WHERE t.`type` = "project" GROUP BY `changwat` HAVING `provname` != "" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($provDb->items as $item) {
		$ret .= '<option value="'.$item->changwat.'" '.($item->changwat==$prov?'selected="selected"':'').'>'.$item->provname.'</option>';
	}
	$ret .= '</select> ';

	// Select ampur
	if ($prov) {
		$ret .= '<select class="form-select" name="ampur" id="input-ampur"><option value="">==ทุกอำเภอ==</option>';
		$stmt = 'SELECT DISTINCT `ampur`,`nameampur` FROM %project_fund% WHERE `changwat`=:prov ORDER BY CONVERT(`nameampur` USING tis620) ASC';
		$dbs = mydb::select($stmt,':prov',$prov);
		foreach ($dbs->items as $item) {
			$ret .= '<option value="'.$item->ampur.'" '.($item->ampur==$ampur?'selected="selected"':'').'>'.$item->nameampur.'</option>';
			
		}
		$ret .= '</select> ';
	}

	$ret .= '<input type="text" name="stext" value="'.htmlspecialchars($searchText).'" placeholder="ระบุคำค้นในชื่อโครงการ" />';

	// Select status
	//$ret.='<select class="form-select"><option>==ทุกสถานะ==</option></select> ';
	$ret .= '&nbsp;&nbsp;<button type="submit" class="btn -primary -no-print"><i class="icon -search -white"></i><span>ดูรายงาน</span></button>&nbsp;<button type="submit" class="btn -no-print" name="export" value="excel"><i class="icon -download"></i><span>Export</span></button>';

	$ret .= '</li>';
	$ret .= '</ul></form>';
	$ret .= '</nav>';

	mydb::where('p.`prtype` = "โครงการ"');
	if ($planid)
		mydb::where('pl.`refid` = :planid',':planid', $planid);
	if ($searchText)
		mydb::where('t.`title` LIKE :search', ':search', '%'.$searchText.'%');
	if ($year)
		mydb::where('p.`pryear`=:year',':year',$year);
	if ($prov)
		mydb::where('p.changwat=:changwat', ':changwat',$prov);
	if ($ampur)
		mydb::where('p.ampur=:ampur', ':ampur',$ampur);


	$label = 'CONCAT("จังหวัด",`provname`)';
	if ($prov) $label = '`title`';
	else if ($ampur) $label = 'CONCAT("กองทุนตำบล",f.`fundname`)';
	else if ($prov) $label = 'CONCAT("อำเภอ",cod.`distname`)';

	$stmt='SELECT
			  p.`tpid`
			, p.`pryear`
			, p.`prid`
			, p.`agrno`
			, t.`title`
			, p.`orgnamedo`
			, o.`name` `fundName`
			, p.`changwat`, cop.`provname` `changwatName`
			, p.`ampur`, cod.`distname` `ampurName`
			, p.`tambon`, cos.`subdistname` `tambonName`
			, p.`area`
			, p.`date_from`
			, p.`date_end`
			, p.`budget`
			, p.`supporttype`
			, p.`project_status`
			, t.`created`
			, (SELECT SUM(`amount`)
				FROM %project_target% tgt
					LEFT JOIN %tag% tg ON tg.`taggroup` = "project:target" AND tgt.`tgtid` = tg.`catid`
				WHERE tgt.`tpid` = p.`tpid` AND tgt.`tagname` = "info" AND tg.`catparent` = 1) `targetAge`
			, (SELECT SUM(`amount`)
				FROM %project_target% tgt
					LEFT JOIN %tag% tg ON tg.`taggroup` = "project:target" AND tgt.`tgtid` = tg.`catid`
				WHERE tgt.`tpid` = p.`tpid` AND tgt.`tagname` = "info" AND tg.`catparent` = 2) `targetSpec`
	FROM %project% p
		LEFT JOIN %topic% t USING(`tpid`)
		LEFT JOIN %db_org% o USING(`orgid`)
		LEFT JOIN %project_tr% pl ON pl.`tpid` = p.`tpid` AND pl.`formid` = "info" AND pl.`part` = "supportplan"
		LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
		LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(p.`changwat`,p.`ampur`)
		LEFT JOIN %co_subdistrict% cos ON cos.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
	%WHERE%
	ORDER BY
		p.`pryear` ASC,
		CONVERT(`changwatName` using tis620) ASC
	';

	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs,'$dbs');

	$ret .= '<p>จำนวน <b>'.number_format($dbs->count()).'</b> โครงการ</p>';

	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->addClass('project-report-name');
	$tables->thead = array(
		'no' => 'ลำดับ',
		'ปี',
		'รหัสโครงการ',
		'ชื่อโครงการ',
		'ผู้รับผิดชอบโครงการ',
		'ชื่อกองทุน',
		'ระยะเวลาดำเนินการ',
		'สถานที่ดำเนินการ',
		'จังหวัด',
		'money -budget' => 'งบประมาณ (บาท)',
		'ประเภทการสนับสนุน',
		'amt -age' => 'กลุ่มเป้าหมายช่วงวัย',
		'amt -spec' => 'กลุ่มเป้าหมายเฉพาะ'
	);

	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			$rs->pryear+543,
			$rs->prid,
			$export?$rs->title:'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
			$rs->orgnamedo,
			$rs->fundName,
			($rs->date_from ? sg_date($rs->date_from, 'ว ดด ปปปป') : '')
			.($rs->date_end ? ' - '.sg_date($rs->date_end, 'ว ดด ปปปป') : ''),
			SG\implode_address($rs, 'short'),
			$rs->changwatName,
			$export ? $rs->budget : number_format($rs->budget,2),
			$rs->supporttype ? 'ประเภท '.$rs->supporttype : '',
			$export ? $rs->targetAge : number_format($rs->targetAge),
			$export ? $rs->targetSpec : number_format($rs->targetSpec),
		);
	}

	if ($export) {
		// file name for download
		$filename = 'project_name_'.date('Y-m-d H-i').".xls";


		die(R::Model('excel.export',$tables,$filename,'{debug:false}'));
		/*
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Type: application/vnd.ms-excel");
		//header('<meta http-equiv="Content-Type" content="text/html; charset='.cfg('client.characterset').'" />');
		//header('<meta http-equiv="Content-Language" content="th" />');

		echo "Project Name"."\n";
		foreach($tables->thead as $row) echo $row."\t";
		echo "\n";
		foreach($tables->rows as $row) {
			if(!$flag) {
				// display field/column names as first row
							//echo implode("\t", array_keys($row)) . "\n";
				$flag = true;
			}
			array_walk($row, 'sg_cleanXlsSepString');
			echo implode("\t", array_values($row)) . "\n";
		}
		die;
		*/
	}

	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');
	//$ret.=print_o(post(),'post');
	head('<script type="text/javascript">
		$(document).on("change","form#project-develop select",function() {
			var $this=$(this)
			if ($this.attr("name")=="prov") $("#input-ampur").val("");
			var para=$this.closest("form").serialize()
			notify("กำลังโหลด")
			location.replace(window.location.pathname+"?"+para)
		});
		</script>');

	$ret .= '<style type="text/css">
	.form-report select {width:120px; margin-right: 4px;}
	</style>';
	return $ret;
}
?>