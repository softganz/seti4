<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_s1ready($self) {
	R::View('project.toolbar', $self, 'โครงการที่สร้างรายงาน ส.1 แล้ว', 'report');

	$stmt='SELECT t.title, tr.* FROM %project_tr% tr
					LEFT JOIN %topic% t USING(tpid)
					WHERE `formid`="ส.1"
					GROUP BY tpid
					ORDER BY tr.created DESC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่สร้างรายงาน','ชื่อโครงการ','จากวันที่','ถึงวันที่');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(sg_date($rs->created,'d-m-ปปปป'),'<a href="'.url('paper/'.$rs->tpid.'/member/owner/post/s1').'">'.$rs->title.'</a>',$rs->date1?sg_date($rs->date1,'d-m-ปปปป'):'',$rs->date2?sg_date($rs->date2,'d-m-ปปปป'):'');
	}

	$ret .= $tables->build();
	return $ret;
}

function _s2() {
	project_model::set_toolbar($self,'โครงการที่สร้างรายงาน ส.2 แล้ว');

	$stmt='SELECT t.title, tr.* FROM %project_tr% tr
					LEFT JOIN %topic% t USING(tpid)
					WHERE `formid`="ส.2"
					GROUP BY tpid
					ORDER BY tr.created DESC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่สร้างรายงาน','ชื่อโครงการ','จากวันที่','ถึงวันที่');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(sg_date($rs->created,'d-m-ปปปป'),'<a href="'.url('paper/'.$rs->tpid.'/member/owner/post/s2').'">'.$rs->title.'</a>',$rs->date1?sg_date($rs->date1,'d-m-ปปปป'):'',$rs->date2?sg_date($rs->date2,'d-m-ปปปป'):'');
	}

	$ret .= $tables->build();
	
	return $ret;
}
?>