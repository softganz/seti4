<?php
/**
* Project Action Join Home
* Created 2019-02-22
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_paidsum($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$doid =  $projectInfo->doingInfo->doid;
	$orderList = array(
		'name' => 'CONVERT(`fullname` USING tis620)',
		'network' => 'CONVERT(`joingroup` USING tis620)',
		'created' => '`created`',
	);

	$showJoinGroup = SG\getFirst(post('group'));
	$showLockOnly = SG\getFirst(post('lk'));
	$searchText = SG\getFirst(post('search'));
	$orderBy = SG\getFirst(post('o'),'created');
	$sortDir = SG\getFirst(post('s'),'d');

	$isMember = $projectInfo->info->membershipType;
	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isOwner = $projectInfo->info->membershipType == 'OWNER' || $isAdmin;
	$isEdit = $isOwner;


	//$ret .= print_o(post(), 'post()');
	// ดูรายการได้เฉพาะผู้ที่มีรายชื่อในโครงการเท่านั้น

	if (!($isMember || $isEdit))
		return $ret;

	$joinGroup = object_merge((object) array('*'=>'== ทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));


	$ret .= '<nav class="nav -page -no-print">';
	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/paidsum'), NULL, 'sg-form form -inlineitem');
	$form->addConfig('method', 'GET');
	//$form->addData('rel', '#main');
	$form->addField('lk', array('type' => 'hidden', 'value' => $showLockOnly));
	$form->addField('o', array('type' => 'hidden', 'value' => $orderBy));
	$form->addField('s', array('type' => 'hidden', 'value' => $sortDir));
	$form->addField(
		'group',
		array(
			'type' => 'select',
			'options' => $joinGroup,
			'value' => $showJoinGroup,
			'attr' => array('onchange' => 'this.form.submit()'),
		)
	);
	$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -search -white"></i>'));
	$ret .= $form->build();
	$ret .= '</nav>';


	mydb::where('ds.`doid` = :doid', ':doid', $doid);
	if ($showJoinGroup && $showJoinGroup != '*')
		mydb::where('ds.`joingroup` = :joingroup', ':joingroup', $showJoinGroup);

	$stmt = 'SELECT
		COUNT(ds.`doid`) `จำนวนผู้ลงทะเบียน`
		, COUNT(IF(ds.`isjoin` = 1 OR ds.`isjoin` = 2 , 1 , NULL)) `จำนวนผู้ผ่านการตรวจสอบเอกสาร`
		, COUNT(IF(ds.`isjoin` = 0 , 1 , NULL)) `จำนวนผู้ไม่ได้รับการตรวจสอบเอกสาร`
		, COUNT(IF(ds.`isjoin` = -1 , 1 , NULL)) `จำนวนผู้ยกเลิกการลงทะเบียน`
		--	, COUNT(IF(ds.`isjoin` = 2 , 1 , NULL)) `จำนวนผู้ผ่านการตรวจสอบเอกสาร2`
		, COUNT(dp.`dopid`) `จำนวนใบสำคัญรับเงิน`
		, COUNT(IF(ds.`isjoin` = 3 , 1 , NULL)) `จำนวนผู้ไม่เบิกจ่าย`
		, COUNT(IF(dp.`islock` = 1 , 1, NULL)) `จำนวนจ่ายเงินเรียบร้อย`
		FROM %org_dos% ds
			LEFT JOIN %org_dopaid% dp ON dp.`doid` = ds.`doid` AND dp.`psnid` = ds.`psnid`
		%WHERE%
		LIMIT 1';
	$dbs = mydb::select($stmt, ':doid', $doid);
	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->thead = array('รายละเอียด', 'amt -hover-parent' => 'จำนวน');
	foreach ($dbs as $key => $value) {
		$menuView = '';
		if ($key == 'จำนวนผู้ยกเลิกการลงทะเบียน') {
			$menuView = '<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/report.cancel').'" data-rel="box" data-width="640"><i class="icon -material">search</i></a>';
		}
		if (substr($key,0,1) != '_')
			$tables->rows[] = array(
				$key,
				number_format($value)
				.'<nav class="nav -icons -hover">'.$menuView.'</nav>'
			);
	}
	$ret .= $tables->build();




	mydb::where('dp.`doid` = :doid', ':doid', $doid);
	if ($showJoinGroup)
		mydb::where('ds.`joingroup` = :joingroup', ':joingroup', $showJoinGroup);
	if ($showLockOnly)
		mydb::where('dp.`islock` > 0');

	mydb::value('$order', $orderList[$orderBy]);
	mydb::value('$sort', $sortDir == 'd' ? 'DESC' : 'ASC');

	$stmt = 'SELECT
		--  dp.*
		 dptr.*
		, ds.`joingroup`
		, pc.`name` `expname`
		, SUM(dptr.`amt`) `sumAmt`
		FROM %org_dopaid% dp
			LEFT JOIN %org_dos% ds ON ds.`doid` = dp.`doid` AND ds.`psnid` = dp.`psnid`
			LEFT JOIN %org_dopaidtr% dptr USING(`dopid`)
			LEFT JOIN %tag% pc ON pc.`taggroup` = "project:expcode" AND pc.`catid` = dptr.`catid`
		--	LEFT JOIN %org_doings% do USING(`doid`)
		%WHERE%
		GROUP BY `joingroup`, `catid`
		HAVING `sumAmt` > 0
		ORDER BY CONVERT(`expname` USING tis620) ASC
		';
	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs, '$dbs');
	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->addClass('-sum-cate');
	$tables->thead['title'] = 'เครือข่าย';
	$tables->tfoot['total']['text'] = 'รวมเงิน';
	foreach ($dbs->items as $rs) {
		$tables->thead[$rs->catid] = $rs->expname;
		$tables->tfoot['total'][$rs->catid] = 0;
	}
	$tables->thead['subtotal'] = 'รวมเงิน';
	$tables->tfoot['total']['grandtotal'] = 0;

	foreach ($dbs->items as $rs) {
		if (!$tables->rows[$rs->joingroup]) {
			foreach ($tables->thead as $catid => $item)
				$tables->rows[$rs->joingroup][$catid] = 0;
			$tables->rows[$rs->joingroup]['title'] = $rs->joingroup;
		}
		$tables->rows[$rs->joingroup][$rs->catid] += $rs->sumAmt;
		$tables->rows[$rs->joingroup]['subtotal'] += $rs->sumAmt;
		$tables->tfoot['total'][$rs->catid] += $rs->sumAmt;
		$tables->tfoot['total']['grandtotal'] += $rs->sumAmt;
	}

	// Set number format
	foreach ($tables->rows as $krow => $row) {
		foreach ($row as $key => $value) {
			if (is_numeric($value))
				$row[$key] = number_format($value,2);
		}
		$tables->rows[$krow] = $row;
	}
	foreach ($tables->tfoot['total'] as $key => $value) {
		if (is_numeric($value))
			$tables->tfoot['total'][$key] = number_format($value,2);
	}

	$ret .= $tables->build();
	//$ret .= print_o($tables, '$tables');

	/*
	$tables = new Table();
	$tables->thead = array(
		'no' => '',
		'name -nowrap' => 'ชื่อ-นามสกุล<a href="'.url('project/join/'.$tpid.'/'.$calId.'/list', array('group'=> $showJoinGroup,'search' => $searchText, 'o' => 'name', 's' => $sortDir == 'a' ? 'd' : 'a')).'"><i class="icon -sort'.($orderBy == 'name' ? '' : ' -gray').'"></i></a>',
		'type -center' => 'เลขประจำตัวบัตรประชาชน',
		'network -nowrap' => 'เครือข่าย<a href="'.url('project/join/'.$tpid.'/'.$calId.'/list', array('group'=> $showJoinGroup,'search' => $searchText, 'o' => 'network', 's' => $sortDir == 'a' ? 'd' : 'a')).'"><i class="icon -sort'.($orderBy == 'network' ? '' : ' -gray').'"></i></a>',
		'travel' => 'เดินทาง',
		'rest -center' => 'ที่พัก',
		'register -date -hover-parent -nowrap' => 'สมัครเมื่อ<a href="'.url('project/join/'.$tpid.'/'.$calId.'/list', array('group'=> $showJoinGroup,'search' => $searchText, 'o' => 'created', 's' => $sortDir == 'a' ? 'd' : 'a')).'"><i class="icon -sort'.($orderBy == 'created' ? '' : ' -gray').'"></i></a>'
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			'<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->psnid).'" data-rel="box">'.trim($rs->paidname).'</a>',
			$rs->joingroup,
			$rs->total,
			$rs->expname,
			$rs->amt,
		);
	}


	$ret .= $tables->build();
	*/


	//$ret .= print_o($dbs, '$dbs');

	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '<style type="text/css">
	.item.-sum-cate td:first-child {white-space: nowrap;}
	.item.-sum-cate td:nth-child(n+2) {text-align: center;}
	.item.-sum-cate .subtotal {font-weight: bold;}
	</style>';
	return $ret;
}
?>