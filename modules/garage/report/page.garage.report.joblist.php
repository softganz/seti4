<?php
/**
* Garage :: Report of Job List
* Created 2018-10-31
* Modify  2021-03-12
*
* @param Object $self
* @return String
*
* @usage garage/report/joblist
*/

$debug = true;

function garage_report_joblist($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');

	$getShopId = post('shop');
	$getInsurer = post('insurer');
	$getFromDate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$getToDate = post('to') ? sg_date(post('to'),'d/m/Y') : date('t/m/Y');

	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}

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
		-- {sum:"totalPrice,replyPrice"}
		';

	$reportDbs = mydb::select($stmt);
	//debugMsg(mydb()->_query);



	// View Model
	$toolbar = new Toolbar($self,'รายงานใบสั่งซ่อมจำแนกตามบริษัทประกัน');
	$self->theme->sidebar=R::View('garage.report.menu');

	$form=new Form(NULL,url('garage/report/joblist'),'','reportform __report_jobcarin -inlineitem');
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
			'value' => post('insurer'),
		)
	);

	$form->addField('go',array('type'=>'button','value'=>'<span>ดูรายงาน</span>'));
	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');

	$toolbar->addNav('main', $form);
	//$ret .= '<nav class="nav -report">'.$form->build().'</nav>';

	$tables = new Table();
	$tables->thead=array(
		'date -rcvdate'=>'วันที่รับรถ',
		'ใบสั่งซ่อม',
		'ทะเบียน',
		'รายละเอียดรถ',
		'insu'.($getInsurer ? ' -hidden':'')=>'บริษัทประกัน',
		'money -totalprice'=>'ราคาเสนอ',
		'money -replyprice'=>'ราคาตกลง',
		'สถานะ',
		''
	);

	foreach ($reportDbs->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->rcvdate,'d/m/ปปปป'),
			$rs->jobno,
			$rs->plate,
			$rs->brandname.' '.$rs->model.$rs->color,
			$rs->insurername,
			$rs->totalPrice?number_format($rs->totalPrice,2):'',
			$rs->replyPrice>0?number_format($rs->replyPrice,2):'',
			GarageVar::$jobStatusList[$rs->jobstatus],
			'<a href="'.url('garage/job/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>'
		);
	}
	$tables->tfoot[]=array($reportDbs->_num_rows,'','','','รวม',number_format($reportDbs->sum->totalPrice,2),number_format($reportDbs->sum->replyPrice,2),'','');
	$ret.=$tables->build();
	$ret.='<p>จำนวนใบสั่งซ่อม '.$reportDbs->_num_rows.' ใบ</p>';
	return $ret;
}
?>