<?php
/**
 * Project report :: Weight Check
 *
 */
function project_report_weightcheck($self) {
	$self->theme->title='รายงานตรวจสอบการบันทึกข้อมูลภาวะโภชนาการ';

	$getTotal = SG\getFirst(post('total'),'less');

	$items = 100;
	$page = post('page');
	$firstRow = $page>1 ? ($page-1)*$items : 0;

	$ui = new Ui();
	$ui->add('<a class="btn" href="'.url('project/report/weightcheck',array('total'=>'less')).'">Total < Get Weight</a>');
	$ui->add('<a class="btn" href="'.url('project/report/weightcheck',array('total'=>'noteq')).'">Total != Get Weight</a>');
	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	mydb::where('p.`prtype`="โครงการ" AND tr.`formid` IN ("weight","height") AND tr.`part` IN ("weight","height")');
	if ($getTotal == 'less') mydb::where('tr.`num1` < tr.`num2`');
	else if ($getTotal == 'noteq') mydb::where('tr.`num1` != tr.`num2`');
	
	if ($zone) {
		mydb::where('LEFT(t.`changwat`,1) IN ( :zone )',':zone','SET:'.$zoneList[$zone]['zoneid']);
		$text[] = ' พื้นที่ '.$zoneList[$zone]['name'];
	}
	if ($province) {
		if (cfg('project.multiplearea')) {
			mydb::where('a.changwat=:changwat ',':changwat',$province);
		} else {
			mydb::where('p.changwat=:changwat ',':changwat',$province);
		}
		$text[] = 'จังหวัด'.mydb::select('SELECT provname FROM %co_province% WHERE provid = :provid LIMIT 1; -- {reset: false}',':provid',$province)->provname;
	}
	if ($year) {
		mydb::where('ti.`detail1` = :year ',':year',$year);
		$text[] = ' ปีการศึกษา '.($year+543);
	}
	if ($term) {
		mydb::where('ti.`detail2` = :term ',':term',$term);
		$text[] = ' ภาคการศึกษา '.($term);
	}
	if ($period) {
		mydb::where('ti.`period` = :period ',':period',$period);
		$text[] = ' ช่วงเวลา '.($periodList[$period]);
	}


	$stmt = 'SELECT
					tr.`trid`
					, tr.`tpid`
					, t.`title`
					, cop.`provname` `provinceName`
					, tr.`sorder`
					, tr.`parent`
					, tr.`part`
					, tr.`detail1` `year`
					, tr.`detail2` `term`
					, tr.`period`
					, tr.`detail3` `area`
					, tr.`detail4` `postby`
					, tr.`date1` `dateinput`
					, qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`num5` `thin`
					, tr.`num6` `ratherthin`
					, tr.`num7` `willowy`
					, tr.`num8` `plump`
					, tr.`num9` `gettingfat`
					, tr.`num10` `fat`
					, tr.`num1` `total`
					, tr.`num2` `getweight`
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_tr% ti ON ti.`trid` = tr.`parent`
						LEFT JOIN %co_province% cop ON t.`changwat` = cop.`provid`
						LEFT JOIN %qt% qt ON qt.`qtgroup` = "schoolclass" AND tr.`sorder` = qt.`qtno`
					%WHERE%
					ORDER BY tr.`tpid`,tr.`sorder` ASC
					';
	$dbs = mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead = array('Project','Province','Tran','Class','Total','GetWeight','Thin','RatherThin','Willowy','Plump','GettingFat','Fat');

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
											'<a href="'.url('project/'.$rs->tpid.'/info.weight/view/'.$rs->parent).'" target="_blank">'.$rs->title.'</a>',
											$rs->provinceName,
											$rs->part,
											$rs->question,
											$rs->total,
											$rs->getweight,
											$rs->thin,
											$rs->ratherthin,
											$rs->willowy,
											$rs->plump,
											$rs->gettingfat,
											$rs->fat,
											);
	}
	$ret .= $tables->build();
	//$ret .= print_o($dbs,'$dbs');
	$ret .= '<style type="text/css">
	.item>tbody>tr>td:nth-child(1) {text-align:left;}
	</style>';
	return $ret;
}
?>