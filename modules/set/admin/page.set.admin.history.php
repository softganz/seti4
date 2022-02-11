<?php
function set_admin_history($self) {
	$stmt='SELECT SUBSTRING(GROUP_CONCAT(FROM_UNIXTIME(h.`created`,"%Y-%m-%d %H-%i-%s") ORDER BY `shid` DESC),1,19) `lcreated`, h.`symbol`, u.`name`, h.`created`, COUNT(*) `total`
					FROM %sethistory% h
						LEFT JOIN %users% u USING (`uid`)
					GROUP BY `symbol`,`username`
					ORDER BY `lcreated` DESC';
	$dbs=mydb::select($stmt);
	$tables = new Table();
	$tables->thead=array('date'=>'Date','Symbol','amt'=>'ครั้ง','User');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
													sg_date($rs->lcreated,'d-m-Y H:i'),
													'<a class="sg-action" href="'.url('set/view/'.$rs->symbol).'" data-rel="#set-info">'.$rs->symbol.'</a>',
													$rs->total,
													$rs->name);
	}
	$ret.=$tables->build();
	return $ret;
}
?>