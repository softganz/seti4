<?php
/**
* Project :: Fund Paiddoc Main Page
* Created 2020-06-08
* Modify  2020-06-08
*
* @param Object $self
* @return String
*
* @usage project/fund/paiddoc
*/

$debug = true;

function project_fund_paiddoc_home($self) {
	$month=SG\getFirst(post('month'),date('Y-m'));
	$isAdmin=user_access('administer projects');

	if (!$isAdmin) return 'SORRY!!! This page is for admin only.';

	// Show summary report
	$thisYearSum=mydb::select('SELECT LEFT(`glcode`,1) `glGroup`,ABS(SUM(`amount`)) `amount` FROM %project_gl% WHERE YEAR(`refdate`)=YEAR(CURDATE()) GROUP BY `glGroup`;-- {key:"glGroup"}')->items;
	$prevYearSum=mydb::select('SELECT LEFT(`glcode`,1) `glGroup`,ABS(SUM(`amount`)) `amount` FROM %project_gl% WHERE YEAR(`refdate`)=YEAR(CURDATE())-1 GROUP BY `glGroup`;-- {key:"glGroup"}')->items;
	$allYearSum=mydb::select('SELECT LEFT(`glcode`,1) `glGroup`,ABS(SUM(`amount`)) `amount` FROM %project_gl% GROUP BY `glGroup`;-- {key:"glGroup"}')->items;

	$ret.='<div class="project-summary">';
	$ret.='<div class="thisyearprojects"><span>รายรับ/รายจ่ายปีนี้</span><p>รายรับ <span class="itemvalue">'.number_format($thisYearSum[4]->amount).'</span><span> บาท</span></p><p>รายจ่าย <span class="itemvalue">'.number_format($thisYearSum[5]->amount).'</span><span> บาท</span></p><p>คงเหลือ <span class="itemvalue">'.number_format($thisYearSum[4]->amount-$thisYearSum[5]->amount).'</span><span> บาท</span></p></div>';
	$ret.='<div class="lastyearprojects"><span>รายรับ/รายจ่ายปีที่แล้ว</span><p>รายรับ <span class="itemvalue">'.number_format($prevYearSum[4]->amount).'</span><span> บาท</span></p><p>รายจ่าย <span class="itemvalue">'.number_format($prevYearSum[5]->amount).'</span><span> บาท</span></p><p>คงเหลือ <span class="itemvalue">'.number_format($prevYearSum[4]->amount-$prevYearSum[5]->amount).'</span><span> บาท</span></p></div>';
	$ret.='<div class="totalprojects"><span>รายรับ/รายจ่ายทั้งหมด</span><p>รายรับ <span class="itemvalue">'.number_format($allYearSum[4]->amount).'</span><span> บาท</span></p><p>รายจ่าย <span class="itemvalue">'.number_format($allYearSum[5]->amount).'</span><span> บาท</span></p><p>คงเหลือ <span class="itemvalue">'.number_format($allYearSum[4]->amount-$allYearSum[5]->amount).'</span><span> บาท</span></p></div>';
	$ret.='</div>';


	$ret.='<nav class="nav -page">';
	$ret.='<form method="get" action="'.url('project/fund/paiddoc').'">ตัวเลือก: ';
	$stmt='SELECT FROM_UNIXTIME(`created`,"%Y-%m") `month`, COUNT(*) `amt` FROM %project_paiddoc% GROUP BY `month` ORDER BY `month` DESC';
	$dbs=mydb::select($stmt);
	$ret.='<select class="form-select" name="month">';
	foreach ($dbs->items as $rs) $ret.='<option value="'.$rs->month.'" '.($rs->month==$month?'selected="selected"':'').'>'.sg_date($rs->month.'-01','ดด ปป').' ('.number_format($rs->amt).' รายการ)</option>';
	$ret.='</select> ';
	$ret.='<button class="btn -primary"><i class="icon -material">search</i><span>ดูรายงาน</span></button>';
	$ret.='</form>';
	$ret.='</nav>';

	mydb::where('FROM_UNIXTIME(pd.`created`,"%Y-%m") = :month',':month',$month);

	$stmt = 'SELECT pd.*
			, t.`title` `projectTitle`
			, t.`orgid`, o.`shortname`
			, o.`name` `orgName`
			FROM %project_paiddoc% pd
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
		%WHERE%
		ORDER BY pd.`created` DESC';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->thead=array('date -refdate'=>'ลงวันที่','เลขที่ใบเบิก','รหัสอ้างอิง','ชื่อโครงการ','money rev'=>'จำนวนเงิน (บาท)','date -created'=>'สร้างเมื่อ','');

	foreach ($dbs->items as $rs) {
		$menu='';
		if ($isAdmin) {
			$menu='<span class="iconset"><a href="'.url('project/'.$rs->tpid.'/info.paiddoc/'.$rs->paidid).'" rel="nofollow"><i class="icon -view"></i><span class="-hidden">รายละเอียด</span></a></span>';
		}
		$tables->rows[] = array(
			sg_date($rs->paiddate,'ว ดด ปป'),
			$rs->docno,
			$rs->refcode,
			'<a href="'.url('project/'.$rs->tpid).'">'.$rs->projectTitle.'</a><br /><em>(<a href="'.url('project/fund/'.$rs->orgid).'">'.$rs->orgName.'</a>)</em>',
			number_format($rs->amount,2),
			sg_date($rs->created,'ว ดด ปป H:i'),
			$menu,
		);
	}
	$ret.=$tables->build();

	//$ret.=print_o($dbs,'$dbs');

	head('<style type="text/css">
	.project-summary {padding:10px;background:#1565C0; color:#fff;}
	.project-summary p {margin:0; padding:0 0 0 16px;}
	.project-summary>div {width:33%; display:inline-block;vertical-align: top;}
	.project-summary>div>span {display:block;}
	.project-summary .itemvalue {font-size: 1.2em; line-height:1.2em;}
	.project-report-section {margin: 16px; padding:8px; float: left; box-shadow: 2px 2px 10px #ccc;}
	.graph-section {width:480px; height:320px;}
	.item.-category {width: 360px; float:left;}
	</style>');
	return $ret;
}
?>