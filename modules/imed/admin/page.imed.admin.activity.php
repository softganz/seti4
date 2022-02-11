<?php
function imed_admin_activity() {
	$ret.='<h3>กิจกรรมการแก้ไขข้อมูล</h3>';

	if (post('uid')) {
		$stmt='SELECT `uid`, `keyword`, `message`, `date`
						FROM %watchdog% w
						WHERE module="imed" AND `uid`=:uid
						ORDER BY `wid` DESC
						LIMIT 1000';
		$dbs=mydb::select($stmt,':uid',post('uid'));

		$tables = new Table();
		$tables->thead=array('Date','Activity','Message');
		$tables->colgrp=array('width="20%"','width="10%"','width="70%"');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(sg_date($rs->date,'d-m-Y H:i:s'),$rs->keyword,$rs->message);
		}
		$ret .= $tables->build();
		return $ret;
	}
	$stmt='SELECT `uid`, `username`, `name`, COUNT(*) total, MAX(`date`) `date`
					FROM %watchdog% w
						LEFT JOIN %users% u USING (uid)
					WHERE module="imed"
					GROUP BY `uid`
					ORDER BY `date` DESC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('no'=>'No','User','amt'=>'Activities','date'=>'Last Activity');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			'<a href="'.url('imed/u/'.$rs->uid).'">'.$rs->name.'</a>',
			'<a class="sg-action" href="'.url('imed/admin/activity',array('uid'=>$rs->uid)).'" data-rel="box">'.$rs->total.'</a>',
			$rs->date
		);
	}

	$ret .= $tables->build();
	
	return $ret;
}
?>