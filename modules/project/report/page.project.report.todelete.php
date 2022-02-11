<?php
/**
* Project request for delete
*
* @param Object $self
* @return String
*/
function project_report_todelete($self) {
	R::View('project.toolbar', $self, 'รายชื่อโครงการแจ้งลบ', 'report');

	$where=array();
	$where=sg::add_condition($where,'t.`status`=:status','status',_DRAFT);
	$where=sg::add_condition($where,'p.`project_status`="ระงับโครงการ"');

	$stmt='SELECT DISTINCT t.`tpid`,t.`title`, o.`name` orgName
						, p.`project_status`
						, u.`username`, u.`name` ownerName
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %db_org% o USING(`orgid`)
						LEFT JOIN %users% u ON t.`uid`=u.`uid`
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					ORDER BY CONVERT(`title` USING tis620) ASC';
	$dbs= mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->thead=array('no'=>'','','ชื่อโครงการ','amt calendarTotals'=>'กิจกรรม(ตามแผน)','amt ownerActivity'=>'กิจกรรมในพื้นที่(ทำแล้ว)','date'=>'กิจกรรมล่าสุด','สถานะโครงการ','หน่วยงาน');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img src="'.model::user_photo($rs->username).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
			'<a href="'.url('paper/'.$rs->tpid).'">'.SG\getFirst($rs->title,'ไม่ระบุชื่อ').'</a>',
			$rs->calendarTotals?$rs->calendarTotals:'-',
			$rs->ownerActivity?$rs->ownerActivity:'-',
			$rs->lastReport?sg_date($rs->lastReport,'ว ดด ปปปป'):'-',
			'รอลบโครงการ',
			$rs->orgName
		);
	}

	$ret .= $tables->build();


	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}
?>