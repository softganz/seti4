<?php
/**
* Project Action Join Home
* Created 2019-03-29
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_duplicatecheck($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$orderList = array(
		'name' => 'CONVERT(`fullname` USING tis620)',
		'network' => 'CONVERT(`joingroup` USING tis620)',
		'created' => '`created`',
	);

	$showJoinGroup = SG\getFirst(post('group'));
	$searchText = SG\getFirst(post('search'));
	$orderBy = SG\getFirst(post('o'),'created');
	$sortDir = SG\getFirst(post('s'),'d');

	$isMember = $projectInfo->info->membershipType;
	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($projectInfo->RIGHT & _IS_EDITABLE) || $isMember;


	//$ret .= print_o(post(), 'post()');
	// ดูรายการได้เฉพาะผู้ที่มีรายชื่อในโครงการเท่านั้น

	if (!($isMember || $isAdmin))
		return $ret;


	$ret .= '<h3>ลงทะเบียนชื่อซ้ำ</h3>';
	$stmt = 'SELECT
		CONCAT(`name`, " ", `lname`) `fullname`, count(*) `amt`
		FROM %org_dos% do
			LEFT JOIN %db_person% p USING(`psnid`)
		WHERE `doid` = :doid
		GROUP BY `fullname`
		HAVING `amt` > 1
		ORDER BY CONVERT(`fullname` USING tis620) ASC';

	$dbs = mydb::select($stmt, ':doid', $projectInfo->doingInfo->doid);

	if ($dbs->count()) {
		$tables = new Table();
		$tables->thead = array('ชื่อ - นามสกุล', 'amt' => 'จำนวนซ้ำ');
		foreach ($dbs->items as $rs) {
			$tables->rows[] = array('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/list', array('search' => $rs->fullname)).'" data-rel="box">'.$rs->fullname.'</a>', $rs->amt);
		}
		$ret .= $tables->build();
	} else {
		$ret .= '<p class="notify">ไม่มีลงทะเบียนชื่อซ้ำ</p>';
	}
	//$ret .= print_o($dbs,'$dbs');


	$ret .= '<h3>ลงทะเบียนเลขบัตรประชาชนซ้ำ</h3>';
	$stmt ='SELECT cid, count(*) `amt`
		FROM %org_dos% do
			LEFT JOIN %db_person% p USING(`psnid`)
		WHERE `doid` =  :doid
		GROUP BY `cid`
		HAVING `amt` > 1
		ORDER BY `amt` DESC';

	$dbs = mydb::select($stmt, ':doid', $projectInfo->doingInfo->doid);
	//$ret .= mydb()->_query;

	if ($dbs->count()) {
		$tables = new Table();
		$tables->thead = array('หมายเลขบัตรประชาชน', 'amt' => 'จำนวนซ้ำ');
		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				'<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/list', array('search' => $rs->cid)).'" data-rel="box">'.SG\getFirst($rs->cid,'ไม่ระบุ').'</a>',
				$rs->amt
			);
		}
		$ret .= $tables->build();
	} else {
		$ret .= '<p class="notify">ไม่มีลงทะเบียนชื่อซ้ำ</p>';
	}

	$ret .= '<h3>ลงทะเบียน ทะเบียนรถซ้ำ</h3>';

	$stmt = 'SELECT `text1` , COUNT(*) amt, GROUP_CONCAT(`refcode`)
,GROUP_CONCAT(CONCAT(p.`name`," ",p.`lname`)) `name`
FROM %project_tr% tr
LEFT JOIN %db_person% p ON tr.`refcode` = p.`psnid`
WHERE refid = :doid AND formid = "join" AND `text1` != ""
GROUP BY text1
HAVING amt > 1';

	$dbs = mydb::select($stmt,  ':doid', $projectInfo->doingInfo->doid);

	$tables = new Table();
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array($rs->text1,$rs->name);
	}
	$ret .= $tables->build();


	$ret .= '<h3>ใบสำคัญรับเงินซ้ำ</h3>';

	$stmt = 'SELECT `psnid`, COUNT(*) `amt`, CONCAT(p.`name`," ",p.`lname`) `fullname`
		FROM %org_dopaid% dp
			LEFT JOIN %db_person% p USING(`psnid`)
		WHERE `doid` = :doid
		GROUP BY `psnid`
		HAVING `amt` > 1';
	$dbs = mydb::select($stmt,  ':doid', $projectInfo->doingInfo->doid);

	$tables = new Table();
	$tables->thead = array('ชื่อ - นามสกุล', 'amt' => 'จำนวนซ้ำ');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'<a href="'.url('project/join/'.$tpid.'/'.$calId.'/money', array('search' => $rs->fullname)).'">'.SG\getFirst($rs->fullname,'ไม่ระบุ').'</a>',
			$rs->amt
		);
	}
	$ret .= $tables->build();


	//$ret .= print_o($dbs,'$dbs');

	$ret .= '<style type="text/css">
	#cboxContent .nav.-page {display: none;}
	</style>';
	return $ret;
}
?>