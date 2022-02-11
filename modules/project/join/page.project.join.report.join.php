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

function project_join_report_join($self, $projectInfo) {
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

	R::View('project.toolbar', $self, 'สรุปการลงทะเบียน - '.$projectInfo->calendarInfo->title, 'join', $projectInfo);

	$joinGroup = object_merge((object) array('*'=>'== ทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));


	$ret .= '<nav class="nav -page -no-print">';
	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/report.join'), NULL, 'sg-form form -inlineitem');
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
		  `joingroup` `เครือข่าย`
		, COUNT(IF(ds.`isjoin` >= 0 , 1 , NULL)) `จำนวนผู้ลงทะเบียน`
		, COUNT(IF(ds.`isjoin` IN (1,2) , 1 , NULL)) `จำนวนผู้ผ่านการตรวจสอบเอกสาร`
		, COUNT(dp.`dopid`) `จำนวนใบสำคัญรับเงิน`
		, COUNT(IF(dp.`islock` = 1 , 1, NULL)) `จำนวนจ่ายเงินเรียบร้อย`
		, COUNT(IF(ds.`isjoin` = 3 , 1 , NULL)) `จำนวนผู้ไม่เบิกจ่าย`

		, COUNT(IF(ds.`isjoin` = 0 , 1 , NULL)) `จำนวนผู้ไม่ได้รับการตรวจสอบเอกสาร`
		, COUNT(IF(ds.`isjoin` = -1 , 1 , NULL)) `จำนวนผู้ยกเลิกการลงทะเบียน`
		FROM %org_dos% ds
			LEFT JOIN %org_dopaid% dp ON dp.`doid` = ds.`doid` AND dp.`psnid` = ds.`psnid`
		%WHERE%
		GROUP BY `joingroup`
		';

	$dbs = mydb::select($stmt, ':doid', $doid);
	//$ret .= print_o($dbs,'$dbs');
	//$ret .= mydb()->_query;



	$tables = new Table();
	$tables->addClass('-sum-cate');
	//$tables->thead['title'] = 'เครือข่าย';
	//$tables->tfoot['total']['text'] = 'รวม';
	foreach (reset($dbs->items) as $key=>$value) {
		$tables->thead[$key] = $key;
		$tables->tfoot['total'][$key] = '-';
	}
	$tables->tfoot['total']['เครือข่าย'] = 'รวม';

	/*
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
	*/

	// Set number format
	foreach ($dbs->items as $item) {
		//$ret .= print_o($item,'$item');
		unset($row);
		foreach ($item as $key => $value) {
			if (is_numeric($value)) {
				$row[$key] = number_format($value);
				$tables->tfoot['total'][$key] += $value;
			} else {
				$row[$key] = $value;
			}
		}
		$tables->rows[] = $row;
	}

	$ret .= $tables->build();


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