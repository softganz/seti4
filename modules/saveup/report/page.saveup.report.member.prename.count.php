<?php
/**
* Saveup :: Report Member Prename Count
* Created 2017-04-16
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/member/prename/count
*/

$debug = true;

function  saveup_report_member_prename_count($self) {
	$self->theme->title='จำนวนสมาชิกตามคำนำหน้าชื่อ';

	$stmt = 'SELECT prename,count(*) AS total FROM %saveup_member% WHERE firstname IS NOT NULL AND status="active" GROUP BY prename ORDER BY total DESC';
	$reports = mydb::select($stmt);


	$tables = new Table();
	$tables->addClass('saveup-report-main');
	$tables->caption=$self->theme->title;
	$tables->thead=array('คำนำหน้าชื่อ','amt'=>'จำนวนคน');
	$total=$no=0;
	foreach ($reports->items as $rs) {
		$tables->rows[]=array($rs->prename,$rs->total);
		$total+=$rs->total;
	}
	$tables->tfoot[]=array('รวม',$total);

	$ret .= $tables->build();
	return $ret;
}
?>