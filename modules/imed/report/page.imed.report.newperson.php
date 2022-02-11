<?php

/**
 * iMed report รายชื่อคนพิการเพิ่มใหม่
 *
 */
function imed_report_newperson($self) {
	$isAdmin=user_access('administer imeds');

	if (!$isAdmin) return message('error','access denied');

	$self->theme->title='รายชื่อเพิ่มใหม่';
	$ret.='<h3>รายชื่อเพิ่มใหม่</h3>';

	$stmt='SELECT p.`psnid` pid, `prename`, CONCAT(p.`name`," ",`lname`) fullname,
						p.`created`, u.`name` createby,
						cod.`distname`, cop.`provname`, cos.`subdistname`
					FROM %db_person% p
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
						LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(p.`changwat`, p.`ampur`)
						LEFT JOIN %co_subdistrict% cos ON cos.`subdistid`=CONCAT(p.`changwat`, p.`ampur`, p.`tambon`)
						LEFT JOIN %users% u ON u.`uid`=p.`uid`
					GROUP BY p.`psnid`
					ORDER BY p.`created` DESC LIMIT 1000';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('date created'=>'วันที่', 'title'=>'ชื่อ - สกุล','จังหวัด','สร้างโดย');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->created,'ว ดด ปปปป'),
			'<a href="'.url('imed', ['pid' => $rs->pid]).'" role="patient" data-pid="'.$rs->pid.'">'.$rs->prename.' '.SG\getFirst($rs->fullname,'...').'</a>',
			SG\implode_address($rs,'short'),
			$rs->createby
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>