<?php
/**
 * Display doings listing
 *
 * @param Integer $mid
 * @return String
 */
function view_org_meeting_doings($psnid) {
	$myorg=org_model::get_my_org();
	if (!$myorg) return;
	$stmt='SELECT d.* , t.`name` as issue
				FROM %org_dos% do
					LEFT JOIN %org_doings% d USING(`doid`)
					LEFT JOIN %tag% t ON t.`tid`=d.`issue`
				WHERE do.`psnid`=:psnid AND do.`isjoin`=1 AND d.`orgid` IN (:myorg) ORDER BY d.`atdate` DESC';
	$dbs=mydb::select($stmt, ':psnid',$psnid, ':myorg', 'SET:'.$myorg);

	$tables = new Table();
	$tables->caption='กิจกรรมที่เข้าร่วม '.$dbs->_num_rows.' ครั้ง';
	$tables->header=array('date'=>'วันที่','กิจกรรม','ประเด็น');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											sg_date($rs->atdate,'ว ดด ปปปป'),
											'<a href="'.url('org/'.$rs->orgid.'/meeting.info/'.$rs->doid).'">'.$rs->doings.'</a>',
											$rs->issue
										);
	}
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>