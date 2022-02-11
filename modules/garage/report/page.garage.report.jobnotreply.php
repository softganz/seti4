<?php
/**
* Garage :: Report Of Job Not Reply Price
* Created 2019-08-07
* Modify  2019-07-13
*
* @param Object $self
* @return String
*
* @usage garage/report/jobnotreply
*/

$debug = true;

function garage_report_jobnotreply($self) {
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
	mydb::where('qt.`replyprice` = 0');
	mydb::where('j.`rcvdate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));
	if ($getInsurer) mydb::where('j.`insurerid` = :insurerid',':insurerid',$getInsurer);

	if ($getShow == 'returned') mydb::where('j.`iscarreturned` = "Yes"');
	else if ($getShow == 'notreturned') mydb::where('j.`iscarreturned` = "No"');

	$stmt = 'SELECT
			j.*
			, b.`brandname`
			, c.`insurername`
			, qt.`qtid`
			, qt.`qtno`
			, qt.`replyprice`
			, SUM(tr.`totalsale`) `totalPrice`
		FROM %garage_qt% qt
			LEFT JOIN %garage_job% j USING(`tpid`)
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %garage_brand% b ON b.`shopid`=j.`shopid` AND b.`brandid`=j.`brandid`
			LEFT JOIN %garage_insurer% c ON c.`insurerid` = qt.`insurerid`
			LEFT JOIN %garage_jobtr% tr USING(`tpid`)
		%WHERE%
		GROUP BY qt.`qtid`
		ORDER BY qt.`tpid` ASC;
		-- {sum:"totalPrice,replyPrice"}
		';

	$reportDbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);



	// View Model
	$toolbar = new Toolbar($self,'รายงานใบเสนอราคายังไม่ตกลงราคา');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/jobnotreply'),'','reportform __report_jobcarin -inlineitem');
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

	$showOptions = array(''=>'=ทุกเงื่อนไข=','returned' => 'คืนรถแล้ว','notreturned'=>'ยังไม่คืนรถ');

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
		'job -nowrap' => 'ใบสั่งซ่อม',
		'ทะเบียน',
		'รายละเอียดรถ',
		'บริษัทประกัน',
		'money -totalprice'=>'ราคาเสนอ',
		''
	);

	foreach ($reportDbs->items as $rs) {
		$tables->rows[]=array(
			$rs->jobno,
			$rs->plate,
			$rs->brandname.' '.$rs->model.$rs->color,
			$rs->insurername,
			$rs->totalPrice?number_format($rs->totalPrice,2):'',
			'<a href="'.url('garage/job/'.$rs->tpid.'/qt/'.$rs->qtid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>'
		);
	}

	$tables->tfoot[] = array('<td colspan="4">รวม '.$reportDbs->_num_rows.' ใบ</td>',number_format($reportDbs->sum->totalPrice,2),'','');

	$ret .= $tables->build();

	return $ret;
}
?>