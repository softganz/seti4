<?php
/**
* Project :: Fund Population Home Page
* Created 2020-04-10
* Modify  2020-04-10
*
* @param Object $self
* @return String
*
* @usage project/fund/population
* @usage project/fund/$orgId/population
*/

$debug = true;

function project_fund_population($self, $fundInfo = NULL) {
	$getChangwat = post('prov');

	// Show summary report
	$thisYearPopulation=mydb::select('SELECT SUM(`num2`) `thisYearPopulation` FROM %project_tr% WHERE `formid`="population" AND YEAR(`date1`)=YEAR(CURDATE()) LIMIT 1')->thisYearPopulation;
	$lastYearPopulation=mydb::select('SELECT SUM(`num2`) `lastYearProjects` FROM %project_tr% WHERE `formid`="population" AND YEAR(`date1`)=YEAR(CURDATE())-1 LIMIT 1')->lastYearPopulation;
	$totalPopulation=mydb::select('SELECT SUM(`population`) `totalPopulation` FROM %project_fund% LIMIT 1')->totalPopulation;

	$ret.='<div class="project-summary">';
	$ret.='<div class="thisyearprojects"><span>ประชากรปีนี้</span><span class="itemvalue">'.number_format($thisYearPopulation).'</span><span>คน</span></div>';
	$ret.='<div class="lastyearprojects"><span>ประชากรปีที่แล้ว</span><span class="itemvalue">'.number_format($lastYearPopulation).'</span><span>คน</span></div>';
	$ret.='<div class="totalprojects"><span>ประชากรทั้งหมด</span><span class="itemvalue">'.number_format($totalPopulation).'</span><span>คน</span></div>';
	$ret.='</div>';

	mydb::where('tr.`formid` = "population"');
	if ($fundInfo) mydb::where('f.`orgid` = :orgid', ':orgid', $fundInfo->orgid);
	if ($getChangwat) mydb::where('f.`changwat` = :prov',':prov', $getChangwat);

	$stmt = 'SELECT tr.`trid`, f.`fundid`, f.`fundname`, f.`nameampur`, f.`namechangwat`
		, YEAR(tr.`date1`) `recordYear`, tr.`num2` `population`, tr.`num3`, tr.`num4`
		, tr.`created`
		FROM %project_tr% tr
			LEFT JOIN %project_fund% f ON tr.`part`=f.`fundid`
		%WHERE%
		ORDER BY tr.`trid` DESC';

	$dbs = mydb::select($stmt);

	$no = 0;
	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead = array(
		'no' => '',
		'ปี พ.ศ.',
		'name -left' => 'ชื่อกองทุน',
		'อำเภอ',
		'จังหวัด',
		'pop -amt' => 'ประชากร(คน)',
		'nhso -money' => 'จัดสรร(บาท)',
		'muni -money' => 'สมทบ(บาท)',
		'created -date' => 'วันที่ป้อนข้อมูล'
	);
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			$rs->recordYear+543,
			'<a href="'.url('project/fund/'.$rs->fundid).'">กองทุนตำบล'.$rs->fundname.'</a>',
			$rs->nameampur,
			$rs->namechangwat,
			number_format($rs->population),
			number_format($rs->num3,2),
			number_format($rs->num4,2),
			sg_date($rs->created,'ว ดด ปป H:i'),
		);
	}
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');

	head('<style type="text/css">
	.project-summary {padding:10px;background:#1565C0; color:#fff;}
	.project-summary>div {width:33%; display:inline-block;}
	.project-summary>div>span {display:block;}
	.project-summary .itemvalue {font-size: 1.8em; line-height:2em;}
	.project-report-section {margin: 16px; padding:8px; float: left; box-shadow: 2px 2px 10px #ccc;}
	.graph-section {width:480px; height:320px;}
	.item.-category {width: 360px; float:left;}
	</style>');
	return $ret;
}
?>