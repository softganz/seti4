<?php
/**
 * iMed report รายชื่อผู้สูงอายุ
 *
 */
function imed_report_elderllst($self) {
	$isAdmin=user_access('administer imeds');

	if (!$isAdmin) return message('error','access denied');

	$self->theme->title='รายชื่อผู้สูงอายุ';
	$ret.='<h3>รายชื่อผู้สูงอาย</h3>';
	$stmt='SELECT c.`pid`, p.`prename`, CONCAT(p.`name`," ",`lname`) fullname,
			p.`created`, u.`name` createby,
			cod.`distname`, cop.`provname`
		FROM %imed_care% c
			LEFT JOIN %db_person% p ON p.`psnid`=c.`pid`
			LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
			LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %users% u ON u.`uid`=p.`uid`
		GROUP BY c.`pid`
		ORDER BY `created` DESC';

	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('no'=>'', 'title'=>'ชื่อ - สกุล','จังหวัด','สร้างโดย','date created'=>'วันที่');
	$no=0;

	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			'<a href="'.url('imed', ['pid' => $rs->pid]).'" role="patient" data-pid="'.$rs->pid.'">'.$rs->prename.' '.SG\getFirst($rs->fullname,'...').'</a>',
			SG\implode_address($rs),
			$rs->createby,
			sg_date($rs->created,'d-m-ปปปป'),
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>