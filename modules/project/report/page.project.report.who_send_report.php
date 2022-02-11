<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_who_send_report($self) {
	R::View('project.toolbar', $self, 'รายงานจำนวนครั้งของการส่งรายงาน', 'report');

	$stmt='SELECT uid,u.name,count(*) comments
					FROM %topic_comments% c
						LEFT JOIN %users% u USING(uid)
					WHERE uid>0
					GROUP BY uid
					ORDER BY comments DESC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','ชื่อ','amt'=>'จำนวนรายงาน');
	$tables->caption='รายงานจำนวนครั้งของการส่งรายงาน';
	$no=0;
	foreach ($dbs->items as $rs) $tables->rows[]=array(++$no,$rs->name,$rs->comments);

	$ret .= $tables->build();

	return $ret;
}
?>