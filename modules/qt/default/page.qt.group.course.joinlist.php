<?php
function qt_group_course_joinlist($self,$uid=NULL) {
	R::View('toolbar',$self,'ข้อมูลทั่วไป','qt.course');

	$stmt='SELECT
					  t.`title`
					, u.`name`
					, CONCAT(p.`name`," ",p.`lname`) `fullname`
					, q.`qtdate`
					, q.`qtref`
					FROM %qtmast% q
					LEFT JOIN %topic% t USING(tpid)
					LEFT JOIN %users% u ON u.`uid`=q.`uid`
					LEFT JOIN %db_person% p ON p.`uid`=q.`uid`
					WHERE qtgroup=10
					ORDER BY title';
	$dbs=mydb::select($stmt);

	foreach ($dbs->items as $key => $rs) {
		$dbs->items[$key]->link='<a href="'.url('qt/group/course/view/'.$rs->qtref).'">View</a>';
	}

	$ret.=mydb::printtable($dbs);
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>