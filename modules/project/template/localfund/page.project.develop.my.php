<?php
/**
 * My relate project such as Owner, Trainer
 *
 * @return String
 */
function project_develop_my($self) {
	R::View('project.toolbar',$self,'พัฒนาโครงการของฉัน','develop');

	$isDevelopProject=false;
	$statusList=project_base::$statusList;

	if (!i()->ok) return '<p class="notify">กรุณาเข้าสู่ระบบสมาชิก</p>'.R::View('signform');

	$ret .= '<nav class="nav -page"><a class="btn -primary" href="'.url('project/develop/nofund/create').'"><i class="icon -addbig -white"></i><span>เขียนโครงการเพื่อขอรับทุน</pan></a></nav>';

	//$ret .= '<h3>พัฒนาโครงการรอตอบรับ</h3>';

	$stmt = 'SELECT d.`tpid`, d.`toorg`, t.`title`, d.`budget`, o.`name`, t.`created`
					FROM %project_dev% d
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %db_org% o ON o.`orgid` = d.`toorg`
					WHERE t.`uid` = :uid AND d.`toorg` IS NOT NULL AND t.`orgid` IS NULL';
	$dbs = mydb::select($stmt, ':uid', i()->uid);

	$tables = new Table();
	$tables->thead = array('no' => '', 'ชื่อโครงการ', 'ชื่อหน่วยงาน', 'budget -money' => 'งบประมาณ', 'create -date' => 'วันที่เริ่มพัฒนา', 'สถานะ');
	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
												++$no,
												'<a href="'.url('project/develop/'.$rs->tpid).'">'.SG\getFirst($rs->title,'???').'</a>',
												'<a href="'.url('project/org/'.$rs->toorg).'">'.$rs->name.'</a>',
												number_format($rs->budget,2),
												sg_date($rs->created, 'ว ดด ปปปป'),
												'รอตอบรับ',
											);
	}

	//$ret .= $tables->build();

	//$ret .= '<h3>พัฒนาโครงการรอส่ง</h3>';

	$stmt = 'SELECT d.`tpid`, d.`toorg`, t.`title`, d.`budget`, t.`created`
					FROM %project_dev% d
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE t.`uid` = :uid AND t.`tpid` IS NOT NULL AND d.`toorg` IS NULL AND t.`orgid` IS NULL AND d.`fundid` IS NULL';
	$dbs = mydb::select($stmt, ':uid', i()->uid);

	//$tables = new Table();
	//$tables->thead = array('no' => '', 'ชื่อโครงการ', 'budget -money' => 'งบประมาณ', 'create -date' => 'วันที่เริ่มพัฒนา');
	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
												++$no,
												'<a href="'.url('project/develop/'.$rs->tpid).'">'.SG\getFirst($rs->title,'???').'</a>',
												'',
												number_format($rs->budget,2),
												sg_date($rs->created, 'ว ดด ปปปป'),
												'รอส่ง',
											);
	}

	//$ret .= $tables->build();


	//$ret .= '<h3>พัฒนาโครงการรอพิจารณา</h3>';

	$stmt = 'SELECT d.`tpid`, d.`toorg`, t.`title`, d.`budget`, t.`created`
					FROM %project_dev% d
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE t.`uid` = :uid AND t.`orgid` IS NOT NULL';
	$dbs = mydb::select($stmt, ':uid', i()->uid);

	//$tables = new Table();
	//$tables->thead = array('no' => '', 'ชื่อโครงการ', 'budget -money' => 'งบประมาณ', 'create -date' => 'วันที่เริ่มพัฒนา');
	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
												++$no,
												'<a href="'.url('project/develop/'.$rs->tpid).'">'.SG\getFirst($rs->title,'???').'</a>',
												'',
												number_format($rs->budget,2),
												sg_date($rs->created, 'ว ดด ปปปป'),
												'รอพิจารณา',
											);
	}

	$ret .= $tables->build();

	$ret .= '<div class="btn-floating -right-bottom"><a class="btn -floating -circle48" href="'.url('project/develop/nofund/create').'"><i class="icon -addbig -white"></i></a></div>';


	$ret .= '<style type="text/css">
	.nav.-submodule.-develop {display: none;}
	</style>';
	return $ret;
}
?>