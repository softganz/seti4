<?php
/**
* Garage report job not reply
* Created 2019-08-07
* Modify  2019-07-13
*
* @param Object $self
* @param $_REQUEST
* @return String
*/

$debug = true;

function garage_report_jobstatus($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');

	$getShopId = post('shop');
	$getInsurer = post('insurer');
	$getFromDate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$getToDate = post('to')?sg_date(post('to'),'d/m/Y'):date('t/m/Y');
	$getShow = post('show');

	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}
	mydb::where('j.`rcvdate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));
	if (post('insurer')) mydb::where('j.`insurerid`=:insurerid',':insurerid',post('insurer'));

	if ($getShow=='notin') mydb::where('j.`carindate` IS NULL');
	else if ($getShow=='returned') mydb::where('j.`iscarreturned` = "Yes"');
	else if ($getShow=='notreturned') mydb::where('j.`iscarreturned` = "No"');
	else if ($getShow=='recieved') mydb::where('j.`isrecieved` = "Yes"');
	else if ($getShow=='notrecieved') mydb::where('j.`isrecieved` = "No"');
	else if ($getShow=='noretdate') mydb::where('j.`datetoreturn` IS NULL AND j.`iscarreturned` = "No"');
	else if ($getShow=='retdate') mydb::where('j.`datetoreturn` IS NOT NULL AND j.`iscarreturned` = "No"');
	else if ($getShow=='closed') mydb::where('j.`isjobclosed` = "Yes"');
	else if ($getShow=='notclosed') mydb::where('j.`isjobclosed` = "No"');

	$stmt = 'SELECT
			j.*
			, GROUP_CONCAT(DISTINCT qt.`qtid`) `qt`
			, GROUP_CONCAT(DISTINCT qt.`billid`) `billing`
			, SUM(qt.`replyprice`) `replyprice`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %garage_qt% qt USING(`tpid`)
			LEFT JOIN %garage_insurer% c ON c.`insurerid` = qt.`insurerid`
			LEFT JOIN %garage_jobtr% tr USING(`tpid`)
		%WHERE%
		GROUP BY j.`tpid`
		ORDER BY j.`jobno` ASC;
		';

	$reportDbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);


	// View Model
	$toolbar = new Toolbar($self,'รายงานสถานะใบสั่งซ่อม '.$reportDbs->_num_rows.' ใบ');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/jobstatus'),'','-report -jobstatus -inlineitem');
	$form->addConfig('method','get');

	$form->addField(
		'shop',
		array(
			'type' => 'select',
			'options' => array('' => '==ทุกสาขา==') + R::Model('garage.shop.branch', $shopInfo->shopId, '{result: "option", value: "shortName"}'),
			'value' => $getShopId,
		)
	);

	$form->addField(
		'from',
		array(
			'label'=>'วันที่รับรถ',
			'type'=>'text',
			'class'=>'sg-datepicker',
			'size'=>10,
			'value'=>$getFromDate,
		)
	);

	$form->addField(
		'to',
		array(
			'label'=>'-',
			'type'=>'text',
			'class'=>'sg-datepicker',
			'size'=>10,
			'value'=>$getToDate,
		)
	);

	$form->addField(
		'insurer',
		array(
			//'label'=>'บริษัทประกัน',
			'type' => 'select',
			'options' => R::Model('garage.insurers', $shopInfo->shopId, array('result' => 'option', 'optionPreList' => array('' => '==ทุกบริษัทประกัน=='))),
			'value' => $getInsurer,
		)
	);


	$showOptions = array(''=>'==ทุกเงื่อนไข==','notin'=>'ยังไม่รับรถ','retdate'=>'นัดรับรถแล้ว','noretdate'=>'ยังไม่นัดรับรถ','returned' => 'คืนรถแล้ว','notreturned'=>'ยังไม่คืนรถ','recieved'=>'รับเงินแล้ว','notrecieved'=>'ยังไม่รับเงิน','closed'=>'จ็อบปิดแล้ว','notclosed'=>'ยังไม่ปิดจ็อบ');

	$form->addField(
		'show',
		array(
			'type' => 'select',
			'options' => $showOptions,
			'value' => $getShow,
		)
	);

	$form->addField('go',array('type'=>'button','value'=>'<i class="icon -material">search</i><span>ดูรายงาน</span>'));

	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');


	$toolbar->addNav('main', $form);

	head('<style type="text/css">
		.item.-report-jobstatus td:nth-child(n+3) {text-align: center;}
		</style>');

	$iconDone = '<i class="icon -material">done</i>';
	$statusList = GarageVar::$jobStatusList;
	unset($statusList[4], $statusList[8]);

	//$ret .= print_o($statusList,'$statusList');

	$tables = new Table();
	$tables->addClass('-report-jobstatus');
	$tables->thead = array('jobno -nowrap'=>'ใบสั่งซ่อม','ทะเบียน');

	foreach ($statusList as $key => $value) {
			$tables->thead['s-'.$key.' -center'] = $value;
		}

	foreach ($reportDbs->items as $rs) {
		$row = array(
			'<a href="'.url('garage/job/'.$rs->tpid).'" target="_blank">'.$rs->jobno.'</a>',
			$rs->plate,
		);

		foreach ($statusList as $i => $statusText) {
			$status=false;
			$row['s-'.$i] = '';
		}

		if ($rs->carindate) $row['s-1'] = $iconDone; else $row['s-0'] = $iconDone;
		if ($rs->qt) $row['s-2'] = $iconDone;
		if ($rs->replyprice > 0) $row['s-3'] = $iconDone;
		$row['s-5'] = $jobProcessList = GarageVar::$jobProcessList[$rs->jobprocess];
		if ($rs->iscarreturned == 'Yes') $row['s-6'] = $iconDone;
		if ($rs->billing) $row['s-7'] = $iconDone;
		if ($rs->isrecieved == 'Yes') $row['s-9'] = $iconDone;
		if ($rs->isjobclosed == 'Yes') $row['s-10'] = $iconDone;

		$tables->rows[] = $row;
	}

	$tables->tfoot[] = array('<td colspan="13">รวม '.$reportDbs->_num_rows.' ใบสั่งซ่อม</td>');

	$ret .= $tables->build();

	//$ret .= print_o($reportDbs, '$reportDbs');

	return $ret;
}
?>