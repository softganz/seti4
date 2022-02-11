<?php
/**
* Garage :: Report Of Job Car Return Date
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage garage/report/jobcarret
*/

$debug = true;

function garage_report_jobcarret($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');

	$getShopId = post('shop');
	$getFromDate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$getToDate = post('to')?sg_date(post('to'),'d/m/Y'):date('t/m/Y');

	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}
	mydb::where('j.`returndate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));

	$stmt = 'SELECT
			j.*
			, b.`brandname`
			, c.`insurername`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %garage_brand% b ON b.`shopid` = j.`shopid` AND b.`brandid` = j.`brandid`
			LEFT JOIN %garage_insurer% c USING(`insurerid`)
		%WHERE%
		GROUP BY j.`tpid`
		ORDER BY j.`rcvdate` ASC;
		';
	$reportDbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);


	// View Model
	$toolbar = new Toolbar($self,'รายงานวันคืนรถ');
	$self->theme->sidebar=R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/jobcarret'),'','reportform __report_jobcarin -inlineitem');
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
			'label'=>'วันที่คืนรถ',
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

	$form->addField('go',array('type'=>'button','value'=>'<i class="icon -search -white"></i>'));
	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');

	$toolbar->addNav('main', $form);

	$tables = new Table();
	$tables->thead=array('date -rcvdate'=>'วันที่รับรถ','date -toreturn'=>'วันคืนรถ','ใบสั่งซ่อม','ทะเบียน','รายละเอียดรถ'/*,'insu -no-print'=>'บริษัทประกัน','status -no-print'=>'สถานะ'*/,'icons -no-print'=>'');
	foreach ($reportDbs->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->rcvdate,'d/m/ปปปป'),
			$rs->returndate ? sg_date($rs->returndate, 'd/m/ปปปป') : '',
			$rs->jobno,
			$rs->plate,
			$rs->brandname
			.($rs->modelname?' '.$rs->modelname:'')
			.($rs->colorname?' สี'.$rs->colorname:''),
			//$rs->insurername,
			//GarageVar::$jobStatusList[$rs->jobstatus],
			'<a href="'.url('garage/job/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>'
		);
	}
	$ret.=$tables->build();
	$ret.='<p>จำนวนใบสั่งซ่อม '.$reportDbs->_num_rows.' ใบ</p>';
	//$ret.=print_o($reportDbs,'$reportDbs');
	return $ret;
}
?>