<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_m1ready($self) {
	R::View('project.toolbar', $self, 'โครงการที่สร้างรายงาน ง.1 แล้ว', 'report');

	$stmt='SELECT t.title, tr.* FROM %project_tr% tr
					LEFT JOIN %topic% t USING(tpid)
					WHERE `formid`="ง.1" AND `part`="title"
					GROUP BY tpid,period
					ORDER BY tr.created DESC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่สร้างรายงาน','งวด','จากวันที่','ถึงวันที่','ชื่อโครงการ');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(sg_date($rs->created,'d-m-ปปปป'),$rs->period,$rs->date1?sg_date($rs->date1,'d-m-ปปปป'):'',$rs->date2?sg_date($rs->date2,'d-m-ปปปป'):'','<a href="'.url('paper/'.$rs->tpid.'/member/owner/post/m1/period/'.$rs->period).'">'.$rs->title.'</a>');
	}
	$ret .= $tables->build();
	return $ret;
}
?>