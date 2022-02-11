<?php

/**
 * Send Document Report
 *
 */
function project_report_monthly($self) {
R::View('project.toolbar', $self, 'รายงานประจำเดือน', 'report');

	$year=SG\getFirst(post('y'));

	$where=array();
	$where=sg::add_condition($where,'tr.`formid`="report" AND tr.`part`="monthly"');
	$stmt='SELECT tr.*, t.`title`, o.`name` orgName, u.`name` ownerName
					FROM %project_tr% tr
						LEFT JOIN %topic% t USING (`tpid`)
						LEFT JOIN %db_org% o USING (`orgid`)
						LEFT JOIN %users% u ON u.`uid`=tr.`uid`
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					ORDER BY tr.`date1` DESC
					';

	$dbs= mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->thead=array('date monthly'=>'ประจำเดือน','ชื่อโครงการ', 'หน่วยงาน','ผู้รายงาน','date created'=>'รายงานเมื่อ');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->date1,'ดดด ปปปป'),
			'<a href="'.url('paper/'.$rs->tpid).'/owner/monthly">'.$rs->title.'</a>',
			$rs->orgName,
			$rs->ownerName,
			sg_date($rs->created,'ว ดด ปปปป'),
		);
		$totalBudgets+=$rs->budget;
	}
	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>