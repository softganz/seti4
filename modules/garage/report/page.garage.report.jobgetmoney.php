<?php
/**
* Garage :: Report Of Job Recieved Money
* Created 2018-07-07
* Modify  2021-04-01
*
* @param Object $self
* @return String
*
* @usage garage/report/jobgetmoney
*/

$debug = true;

function garage_report_jobgetmoney($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');

	$getShopId = post('shop');
	$getInsurer = post('insurer');
	$getFromDate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$getToDate = post('to')?sg_date(post('to'),'d/m/Y'):date('t/m/Y');

	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}
	mydb::where('(j.`rcvmoneyamt` > 0 OR j.`isrecieved` = "Yes")');
	mydb::where('j.`rcvdate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));
	if ($getInsurer) mydb::where('j.`insurerid` = :insurerid',':insurerid',$getInsurer);

	$stmt = 'SELECT
		j.*
		, b.`brandname`
		, c.`insurername`
		, SUM(tr.`totalsale`) `totalPrice`
		, (SELECT SUM(`replyprice`) FROM %garage_qt% q WHERE q.`tpid`=j.`tpid`) `replyPrice`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %garage_brand% b ON b.`shopid`=j.`shopid` AND b.`brandid`=j.`brandid`
			LEFT JOIN %garage_insurer% c USING(`insurerid`)
			LEFT JOIN %garage_jobtr% tr USING(`tpid`)
		%WHERE%
		GROUP BY j.`tpid`
		ORDER BY j.`datetoreturn` ASC;
		-- {sum:"rcvmoneyamt,replyPrice"}
		';

	$reportDbs = mydb::select($stmt);
	
	//debugMsg(mydb()->_query);



	// View Model
	$toolbar = new Toolbar($self,'รายงานใบสั่งซ่อมรับเงินแล้ว');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/jobgetmoney'),'','reportform __report_jobcarin -inlineitem');
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

	$form->addField(
		'insurer',
		array(
			//'label'=>'บริษัทประกัน',
			'type' => 'select',
			'options' => R::Model('garage.insurers', $shopInfo->shopId, array('result' => 'option', 'optionPreList' => array('' => '==ทุกบริษัทประกัน=='))),
			'value' => $getInsurer,
		)
	);

	$form->addField('go',array('type'=>'button','value'=>'<span>ดูรายงาน</span>'));

	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');

	$toolbar->addNav('main', $form);

	$tables = new Table();
	$tables->thead = array(
		'ใบสั่งซ่อม',
		'date -rcvdate'=>'วันรับเงิน',
		'ทะเบียน',
		'บริษัทประกัน',
		'money -replyprice'=>'ราคาตกลง',
		'money -totalrcv'=>'รับเงิน',
		'center -status'=>'',
		'icons'=>''
	);

	foreach ($reportDbs->items as $rs) {
		$tables->rows[]=array(
			$rs->jobno,
			$rs->rcvmoneydate?sg_date($rs->rcvmoneydate,'d/m/ปปปป'):'',
			$rs->plate,
			$rs->insurername,
			$rs->replyPrice>0?number_format($rs->replyPrice,2):'',
			$rs->rcvmoneyamt?number_format($rs->rcvmoneyamt,2):'',
			$rs->isrecieved=='Yes'?'✓':'!',
			'<a href="'.url('garage/job/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>'
		);
	}
	$tables->tfoot[]=array('','','','รวม '.$reportDbs->_num_rows.' รายการ',number_format($reportDbs->sum->replyPrice,2),number_format($reportDbs->sum->replyPrice,2),'','');

	$ret .= $tables->build();

	return $ret;
}
?>