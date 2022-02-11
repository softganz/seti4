<?php
/**
* Project :: Employee Withdraw Report
* Created 2021-02-24
* Modify  2021-02-24
*
* @param Object $self
* @param Object $projectInfo
* @param Int $period
* @return String
*
* @usage project/{id}/info.employee.withdraw_report/{$period}
*/

$debug = true;

function project_info_employee_withdraw_report($self, $projectInfo, $period) {
	// Data Model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$getPeriod = post('period');
	$getStatus = post('status');
	$getTambon = post('tambon');
	$getReportType = SG\getFirst(post('type'),1);
	$getExport = post('export');

	$isAdmin = is_admin();
	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'Access Denied');

	$cfgFollow = cfg('project')->follow;
	$periodList = R::Model('project.period.get', $projectId);

	if (empty($getPeriod)) {
		$getPeriod = ($lastPeriod = mydb::select('SELECT
			  period.`period`, `date2` `dateEnd`, SUM(period.`num2`) `paidAmt`, COUNT(*) `projects`
			FROM %project_tr% period
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %topic% tp ON tp.`tpid` = t.`parent`
			WHERE tp.`parent` = :parent AND period.`period` > 0 AND period.`num2` > 0
			GROUP BY `period`
			ORDER BY `period` DESC
			LIMIT 1
			',
			':parent', $projectId
		))->period;
	}
	//debugMsg($lastPeriod, '$lastPeriod');

	if (empty($getPeriod)) return message('error', 'ขออภัย ยังไม่มีการกำหนดงวดของรายงาน');

	// Get Approved of Period
	mydb::where('p.`project_status` = "กำลังดำเนินโครงการ" AND tp.`parent` = :parent', ':parent', $projectId);
	mydb::where('p.`ownertype` IN ( :ownerType )', ':ownerType', 'SET-STRING:'.implode(',', [_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE]));
	mydb::where('period.`formid` = "info" AND period.`part` = "period" AND period.`period` = :period AND period.`flag` = :flag', ':period', $getPeriod, ':flag', _PROJECT_PERIOD_FLAG_GRANT);
	if ($getStatus == 'no') {
		mydb::where('c.`fldref` IS NULL');
	} else if ($getStatus == 'nobank') {
		mydb::where('(p.`bankno` IS NULL OR p.`bankno` = "")');
	} else if ($getStatus == 'nocid') {
		mydb::where('(pn.`cid` IS NULL OR pn.`cid` = "")');
	} else if ($getStatus) {
		mydb::where('c.`fldref` = :status', ':status', $getStatus);
	}
	if ($getTambon) {
		mydb::where('t.`parent` = :tambon', ':tambon', $getTambon);
	}

	$stmt = 'SELECT
		  period.`tpid` `projectId`, period.`period`, period.`num2` `paidAmt`
		, t.`title` `projectTitle`
		, t.`areacode`, t.`uid`
		, p.`ownertype`
		, p.`bankaccount`, p.`bankno`, p.`bankname`
		, tp.`title` `parentTitle`
		, c.`fldref` `bankChackStatus`
		, cop.`provname` `changwatName`
		, cod.`distname` `ampurName`
		, cos.`subdistname` `tambonName`
		, CONCAT(pn.`prename`,pn.`name`," ",pn.`lname`) `employeeName`
		, pn.`cid`
		, pn.`house` `employeeHouse`
		, pn.`areacode` `employeeAreacode`
		, SUBSTRING(pn.`areacode`,7,2) `employeeVillage`
		, copn.`provname` `employeeChangwatName`
		, codn.`distname` `employeeAmpurName`
		, cosn.`subdistname` `employeeTambonName`
		FROM %project_tr% period
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_person% pn ON pn.`userid` = t.`uid`
			LEFT JOIN %topic% tp ON tp.`tpid` = t.`parent`
			LEFT JOIN %bigdata% c ON c.`keyname` = "project.info" AND c.`keyid` = period.`tpid` AND c.`fldname` = "bankcheck"
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(t.`areacode`, 4)
			LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(t.`areacode`, 6)
			LEFT JOIN %co_province% copn ON copn.`provid` = LEFT(pn.`areacode`, 2)
			LEFT JOIN %co_district% codn ON codn.`distid` = LEFT(pn.`areacode`, 4)
			LEFT JOIN %co_subdistrict% cosn ON cosn.`subdistid` = LEFT(pn.`areacode`, 6)
		%WHERE%
		ORDER BY
		CONVERT(`changwatName` USING tis620) ASC
		, CONVERT(`ampurName` USING tis620) ASC
		, CONVERT(`tambonName` USING tis620) ASC
		, `ownertype` ASC
		, CONVERT(`employeeName` USING tis620) ASC;
		-- {sum: "paidAmt"}
		';

	$approvedDbs = mydb::select($stmt);
	//debugMsg(mydb()->_query);


	// View Model
	$toolbar = new Toolbar($self, $projectInfo->title);

	$form = new Form(
		[
			'action' => url('project/'.$projectId.'/info.employee.withdraw.report'),
			'class' => 'sg-form -sg-flex -justify-left',
			'data-rel' => '#main',
			'children' => [
				'period' => [
					'type' => 'select',
					'options' => (function($periodList) {array_walk($periodList, function (&$value, $key) {$value = 'งวดที่ '.$key.'-'.sg_date($value->dateEnd, 'ดด ปป');}); return $periodList;})($periodList),
					'value' => $getPeriod,
				],
				'status' => [
					'type' => 'select',
					'options' => array('' => 'ทั้งหมด', '1' => 'แก้ไข', '9' => 'ยืนยัน', 'no' => 'ยังไม่ปรับปรุง','nobank' => 'ไม่มีเลขบัญชี','nocid' => 'ไม่มีเลข 13 หลัก'),
					'value' => $getStatus,
				],
				'tambon' => [
					'type' => 'select',
					'class' => '-fill',
					'options' => array('' => 'ทุกตำบล')
						+ R::Model(
							'project.follows',
							'{childOf: '.$projectId.', ownerType: "'._PROJECT_OWNERTYPE_TAMBON.'"}',
							'{items: "*", order: "CONVERT(t.`title` USING tis620)", sort: "ASC", key: "projectId", value: "title", debug: false}'
						)->items,
					'value' => $getTambon,
					'container' => '{style: "width:100px;"}',
				],
				'type' => [
					'type' => 'select',
					'options' => [1 => 'การเบิกจ่ายเงินเดือน', 2 => 'ที่อยู่สำหรับหักภาษี'],
					'value' => $getReportType,
				],
				'go' => [
					'type' => 'button',
					'value' => '<i class="icon -material">search</i>'
				],
				'export' => [
					'type' => 'button',
					'name' => 'export',
					'class' => '-secondary',
					'value' => 'export',
					'text' => '<i class="icon -material">cloud_download</i>',
				]
			]
		]
	);
	$toolbar->addNav('form', $form);

	$ret = '<div class="x-header -sg-text-center"><h3>รายงานข้อมูลเบิกจ่ายเงินเดือน '.(($periodDate = $periodList[$getPeriod]->dateEnd) ? sg_date($periodDate, 'ดดด ปปปป') : 'งวดที่ '.$getPeriod).'<br />'.$projectInfo->title.'</h3></div>';

	$tables = new Table();
	if ($getReportType == 1) {
		$tables->thead = [
			'no -no' => 'ลำดับที่',
			'name -nowrap' => 'ชื่อ สกุล',
			'cid -center' => 'เลขบัตรประชาชน',
			'type -center' => 'ประเภทการจ้างงาน',
			'changwat -center -nowrap' => 'จังหวัด',
			'ampur -center -nowrap' => 'อำเภอ',
			'tambon -center -nowrap' => 'ตำบล',
			'paid -money' => 'จำนวนเงินเบิกจ่าย',
			'nabkno -center' => 'เลขที่บัญชี',
			'bank -center' => 'ธนาคาร',
			'icons -noprint' => '',
		];
	} else {
		$tables->thead = [
			'cid' => 'เลขประจำตัวประชาชน',
			'name -nowrap' => 'ชื่อ สกุล',
			'address' => 'ที่อยู่',
			'paid -money -nowrap' => 'จำนวนเงิน',
		];
	}
	//$tables->addConfig('showHeader', false);

	$no = 0;
	$currentParentTitle = '';
	foreach ($approvedDbs->items as $rs) {
		/*
		if ($rs->parentTitle != $currentParentTitle) {
			$tables->rows[] = array('<th colspan="10">'.$rs->parentTitle.'</th>');
			$currentParentTitle = $rs->parentTitle;
			$tables->rows[] = '<header>';
		}
		*/

		switch ($rs->bankChackStatus) {
			case 9: $checkIcon = '<i class="icon -material -sg-active">check_circle</i>'; break;
			case 1: $checkIcon = '<i class="icon -material -sg-active">check_circle_outline</i>'; break;
			default: $checkIcon = '<i class="icon -material -sg-inactive">check_circle_outline</i>'; break;
		}

		if ($getReportType == 1) {
			$tables->rows[] = [
				++$no,
				$rs->bankaccount.(empty($rs->bankaccount) ? '<em>('.$rs->projectTitle.')</em>' : ''),
				$rs->cid,
				$cfgFollow->ownerType->{$rs->ownertype}->title,
				$rs->changwatName,
				$rs->ampurName,
				$rs->tambonName,
				number_format($rs->paidAmt,2),
				$rs->bankno,
				$rs->bankname,
				$getExport ? '' : '<nav class="nav -icons"><ul><li><a class="btn -link">'.$checkIcon.'</a></li>'
				. '<li><a class="sg-action btn -link" href="'.url('project/'.$projectId.'/info.child.bank.edit/'.$rs->projectId).'" data-rel="box" data-width="480"><i class="icon -material">edit</i></a></li></ul></nav>',
			];
		} else if ($rs->paidAmt >= 15000) {
			$employeeAddress = SG\implode_address(
				[
					'house' => $rs->employeeHouse,
					'village' => $rs->employeeVillage,
					'tambonName' => $rs->employeeTambonName,
					'ampurName' => $rs->employeeAmpurName,
					'changwatName' => $rs->employeeChangwatName,
				]
			);

			$tables->rows[] = [
				$rs->cid,
				$rs->employeeName,
				$employeeAddress,
				number_format($rs->paidAmt,2),
			];
		}
	}

	if ($getReportType == 1) {
		$tables->rows[] = [
			'<td></td>',
			'<td colspan="6"><div class="-sg-text-right"><b>รวม '.$approvedDbs->count().' คน จำนวนเงิน</b></td>',
			'<b>'.number_format($approvedDbs->sum->paidAmt,2).'</b></div>',
			'<b>บาท</b>',
			'',
			'',
		];
	}

	if ($getExport) {
		// file name for download
		$filename = 'project_employee_paid_'.date('Y-m-d H-i').".xls";

		die(R::Model('excel.export',$tables, $filename, '{debug:false}'));
	}

	$ret .= '<div style="overflow: auto;">'.$tables->build().'</div>';

	$ret .= '<nav class="-noprint">'.$form->build().'</nav>';

	$ret .= '<div class="-noprint"><i class="icon -material -sg-active">check_circle</i> ยืนยันข้อมูลธนาคารแล้ว<br /><i class="icon -material -sg-active">check_circle_outline</i> แก้ไขบัญชีธนาคารแล้ว<br /><i class="icon -material -sg-inactive">check_circle_outline</i> ยังไม่ได้เข้ามายืนยัน/แก้ไข</div>';

	//$ret .= print_o($approvedDbs, '$approvedDbs');

	//debugMsg($periodList, '$periodList');
	head('<style type="text/css">
	.sg-toolbar.-main>.nav .form-select {width: 90px;}
	@media print {
	.item td {font-size: 0.9em; white-space: no-wrap;}
	</style>
	}');

	return $ret;
}
?>