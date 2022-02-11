<?php
/**
* Garage :: Report Of Billing Not Recieve Money
* Created 2018-10-31
* Modify  2021-04-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage garage/report/billnotmoney
*/

$debug = true;

function garage_report_billnotmoney($self) {
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
	mydb::where('b.`billstatus` = 1 AND q.`rcvid` IS NULL');
	//mydb::where('b.`billdate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));
	if ($getInsurer) mydb::where('b.`insurerid` = :insurerid',':insurerid',$getInsurer);
	if ($getShow == 'carin') mydb::where('j.`carindate` IS NOT NULL');
	else if ($getShow == 'notin') mydb::where('j.`carindate` IS NULL');
	else if ($getShow == 'returned') mydb::where('j.`iscarreturned` = "Yes"');
	else if ($getShow == 'notreturned') mydb::where('j.`iscarreturned` = "No"');

	$stmt = 'SELECT
			b.*
			, i.`insurername`
			, SUM(q.`replyprice`) `totalPrice`
			, GROUP_CONCAT(j.`jobno`) `jobNoList`
			, GROUP_CONCAT(q.`rcvid`) `rcvid`
		FROM %garage_billing% b
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
			LEFT JOIN %garage_qt% q USING(`billid`)
			LEFT JOIN %garage_job% j USING(`tpid`)
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
		%WHERE%
		GROUP BY b.`billid`
		ORDER BY b.`billdate` ASC;
		-- {sum:"totalPrice"}
		';

	$reportDbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);



	// View Model
	$toolbar = new Toolbar($self,'รายงานใบวางบิลยังไม่รับเงิน');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/billnotmoney'),'','reportform __report_jobcarin -inlineitem');
	$form->addConfig('method','get');

	$form->addField(
		'shop',
		array(
			'type' => 'select',
			'options' => array('' => '==ทุกสาขา==') + R::Model('garage.shop.branch', $shopInfo->shopId, '{result: "option", value: "shortName"}'),
			'value' => $getShopId,
		)
	);

	/*
	$form->addField(
		'from',
		array(
			'label'=>'วันที่วางบิล',
			'type'=>'text',
			'class'=>'sg-datepicker',
			'value'=>$getFromDate,
			)
		);
	$form->addField(
		'to',
		array(
			'label'=>'-',
			'type'=>'text',
			'class'=>'sg-datepicker',
			'value'=>$getToDate,
			)
		);
	*/

	$form->addField(
		'insurer',
		array(
			//'label'=>'บริษัทประกัน',
			'type' => 'select',
			'options' => R::Model('garage.insurers', $shopInfo->shopId, array('result' => 'option', 'optionPreList' => array('' => '==ทุกบริษัทประกัน=='))),
			'value' => $getInsurer,
		)
	);

	$showOptions = array(''=>'=ทุกเงื่อนไข=','carin'=>'เข้าซ่อมแล้ว','notin'=>'ยังไม่เข้าซ่อม','returned' => 'คืนรถแล้ว','notreturned'=>'ยังไม่คืนรถ');
	$form->addField(
		'show',
		array(
			'type' => 'select',
			'options' => $showOptions,
			'value' => $getShow,
		)
	);

	$form->addField('go',array('type'=>'button','value'=>'<span>ดูรายงาน</span>'));
	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');

	$toolbar->addNav('main', $form);

	$tables = new Table();
	$tables->thead = array(
		'ใบวางบิล',
		 'date -billdate'=>'วันที่วางบิล',
		'insu'.($getInsurer ? ' -hidden':'')=>'บริษัทประกัน',
		// 'money -totalprice'=>'ราคาเสนอ',
		'money -replyprice'=>'จำนวนเงิน	',
		// 'สถานะ',
		''
	);
	foreach ($reportDbs->items as $rs) {
		$tables->rows[]=array(
			$rs->billno,
			sg_date($rs->billdate,'d/m/ปปปป'),
			$rs->insurername,
			$rs->totalPrice > 0 ? number_format($rs->totalPrice,2) : '',
			// GarageVar::$jobStatusList[$rs->jobstatus],
			'<a class="sg-action" href="'.url('garage/billing/view/'.$rs->billid).'" data-rel="box" data-width="640" title=""><i class="icon -viewdoc"></i></a>'
		);
	}
	$tables->tfoot[] = array(
		'',
		'',
		'',
		number_format($reportDbs->sum->totalPrice,2),
		// number_format($reportDbs->sum->replyPrice,2),
		// '',
		''
	);

	$ret .= $tables->build();

	return $ret;
}
?>