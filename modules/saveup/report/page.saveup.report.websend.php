<?php
/**
* Saveup :: Report Payment Send
* Created 2018-03-01
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/websend
*/

$debug = true;

function saveup_report_websend($self) {
	$self->theme->title = 'รายงานจำนวนครั้งในการแจ้งโอนเงินผ่านเว็บ';
	$year = SG\getFirst(post('y'),date('Y'));
	$order = SG\getFirst(post('o'),'times');

	$orderList = array(
		'times' => '`times` DESC',
		'poster' => 'CONVERT(`poster` USING tis620) ASC',
	);

	$stmt='SELECT DISTINCT FROM_UNIXTIME( created, "%Y" ) cyear FROM %saveup_log% ORDER BY `cyear` DESC';
	$yearDbs=mydb::select($stmt);

	$ret.='<div class="toolbar">';
	foreach ($yearDbs->items as $rs) {
		$ret.='<a href="'.url('saveup/report/websend',array('y'=>$rs->cyear)).'">'.($rs->cyear+543).'</a> | ';
	}
	$ret.='</div>';

	$stmt = 'SELECT
		FROM_UNIXTIME( created, "%Y-%m-01" ) `cmonth`
		, COUNT( * ) `times`, SUM(`amt`) `totals`
		FROM %saveup_log%
		WHERE keyword = "TRANSFER"
		AND FROM_UNIXTIME( created, "%Y" )=:year
		GROUP BY `cmonth`
		ORDER BY `cmonth` ASC;
		-- {sum:"times,totals"}';
	$dbs=mydb::select($stmt,':year',$year);

	$tables = new Table();
	$tables->addClass('-center');
	$tables->caption='รายงานจำนวนครั้งในการแจ้งโอนเงินผ่านเว็บรายเดือน ประจำปี '.($year+543);
	$tables->thead=array('เดือน-ปี','จำนวนครั้ง','จำนวนเงิน');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(sg_date($rs->cmonth,'ดดด ปปปป'),number_format($rs->times),number_format($rs->totals,2));
	}
	$tables->tfoot[]=array('รวม',number_format($dbs->sum->times),number_format($dbs->sum->totals,2));
	$ret.=$tables->build();

	$stmt='SELECT
		FROM_UNIXTIME( created, "%Y" ) `cyear`
		, `poster`, COUNT( * ) `times`, SUM(`amt`) `totals`
		FROM %saveup_log%
		WHERE keyword = "TRANSFER"
		AND FROM_UNIXTIME( created, "%Y" )=:year
		GROUP BY cyear, poster
		ORDER BY '.$orderList[$order];
	$dbs=mydb::select($stmt,':year',$year);
	//$ret .= mydb()->_query;

	$no=0;
	$total=0;
	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','ชื่อ-นามสกุล','amt'=>'จำนวนครั้ง','money'=>'จำนวนเงิน(บาท)');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(++$no,$rs->poster,$rs->times,number_format($rs->totals,2));
		$total+=$rs->times;
		$totalMoney+=$rs->totals;
	}
	$tables->tfoot[]=array('<td></td>','รวมทั้งสิ้น',$total,number_format($totalMoney,2));
	$ret.=$tables->build();
	return $ret;
}
