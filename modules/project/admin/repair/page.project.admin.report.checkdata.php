<?php
function project_admin_report_checkdata($self) {
	R::View('project.toolbar',$self,'ตรวจสอบข้อมูลผิดพลาด','admin');
	$self->theme->sidebar=R::View('project.admin.menu','report');

	$stmt='SELECT e.`trid`, e.`tpid`, e.`gallery`, e.`num1`, e.`num2`, e.`num3`, e.`num4`, e.`text1`
			, m.`trid` mainActID
		FROM `sgz_project_tr` e
			LEFT JOIN `sgz_project_tr` m ON m.`trid`=e.`parent`
		WHERE e.`formid`="develop" AND e.`part` LIKE "exptr"
		HAVING mainActID IS NULL';
	$dbs=mydb::select($stmt);
	$ret.='<h3>ค่าใช้จ่ายที่ไม่มีกิจกรรมหลัก</h3>';
	if ($dbs->_empty) $ret.='<p class="notify">ไม่มีรายการ</p>';
	$ret.=mydb()->printtable($dbs);

	$stmt = 'SELECT e.`trid`, e.`tpid`, t.`title`, e.`parent` `mainActId`
			, e.`num1`*e.`num2`*e.`num3` `caltotal`, e.`num4` `total`
			, m.`detail1` mainActTitle
		FROM `sgz_project_tr` e
			LEFT JOIN sgz_topic t USING(tpid)
			LEFT JOIN %project_tr% m ON m.`trid`=e.`parent`
		WHERE e.`formid`="develop" AND e.`part` LIKE "exptr"
		HAVING `caltotal`!=`total`';

	$dbs = mydb::select($stmt);

	$ret.='<h3>ค่าใช้จ่ายที่คำนวณยอดรวมไม่ถูกต้อง</h3>';
	if ($dbs->_empty) $ret.='<p class="notify">ไม่มีรายการ</p>';
	$tables = new Table();
	$tables->thead=array('trid','tpid','โครงการ','กิจกรรมหลัก','mainActId','ค่าใช้จ่ายคำนวณ','ค่าใช้จ่ายในฐานข้อมูล');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->trid,$rs->tpid,'<a href="'.url('project/develop/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a>',$rs->mainActTitle,$rs->mainActId,number_format($rs->caltotal,2),number_format($rs->total,2));
	}
	$ret.=$tables->build();

	$ret.='<h3>บันทึกกิจกรรมซ้ำ (มากกว่า 1 ครั้ง)</h3>';
	$stmt = 'SELECT
		a.`trid`, a.`tpid`, a.`calid`
		, COUNT(*) amt
		, t.`title`
		, c.`title` calendarTitle
		, p.`pryear`
		FROM %project_tr% a
			LEFT JOIN %topic% t ON t.`tpid`=a.`tpid`
			LEFT JOIN %calendar% c ON c.`id`=a.`calid`
			LEFT JOIN %project% p ON p.`tpid`=a.`tpid`
		WHERE `formid`="activity" and `part`="owner"
		GROUP BY `calid`
		HAVING amt>1
		ORDER BY `pryear` DESC, a.`trid` DESC';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('พ.ศ.','trid','tpid','โครงการ','กิจกรรม','amt'=>'จำนวนครั้ง');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->pryear+543,
			$rs->trid,
			$rs->tpid,
			'<a href="'.url('project/'.$rs->tpid.'/info.action').'" target="_blank">'.$rs->title.'</a>',
			$rs->calendarTitle,
			$rs->amt
		);
	}

	$ret .= $tables->build();

	// บันทึกกิจกรรมที่ไม่มีปฏิทินกิจกรรม
	$stmt = 'SELECT t.trid,t.tpid,formid,part,date1,t.calid,c.id,c.title,c.from_date
			, p.`pryear`
			, tp.`title` `projectTitle`
		FROM %project_tr% t
			LEFT JOIN %calendar% c ON c.id=t.calid
			LEFT JOIN %project% p ON p.`tpid`=t.`tpid`
			LEFT JOIN %topic% tp ON tp.`tpid`=t.`tpid`
		WHERE `formid` LIKE "activity" AND c.id IS NULL
		ORDER BY t.`date1`  ASC
		';

	$dbs = mydb::select($stmt);

	$ret.='<h3>บันทึกกิจกรรมที่ไม่มีปฏิทินกิจกรรม</h3>';
	$ret.=mydb::printtable($dbs);
	return $ret;
}
?>