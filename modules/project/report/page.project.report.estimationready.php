<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_estimationready($self) {
	R::View('project.toolbar', $self, 'โครงการที่สร้างรายงานประเมินแล้ว', 'report');

	$stmt = 'SELECT t.title, tr.*, COUNT(*) itemcount FROM %project_tr% tr
		LEFT JOIN %topic% t USING(tpid)
		WHERE `formid`="ประเมิน"
		GROUP BY tpid
		ORDER BY tr.created DESC';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่สร้างรายงาน','ชื่อโครงการ','amt'=>'จำนวนรายการ');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(sg_date($rs->created,'d-m-ปปปป'),'<a href="'.url('project/'.$rs->tpid.'/eval.valuation').'">'.$rs->title.'</a>',$rs->itemcount);
	}

	$ret .= $tables->build();
	return $ret;
}
?>