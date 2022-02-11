<?php
function garage_report_jobcardate($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');

	$getShopId = post('shop');
	$getFromDate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$getToDate = post('to') ? sg_date(post('to'),'d/m/Y') : date('t/m/Y');
	$getInsurer = post('insurer');

	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}
	if (post('st')) mydb::where('j.`iscarreturned` = "No"');
	mydb::where('j.`datetoreturn` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));
	if ($getInsurer) mydb::where('j.`insurerid` = :insurerid',':insurerid',$getInsurer);

	$stmt = 'SELECT
			j.*
			, b.`brandname`
			, c.`insurername`
			, j.`modelname`, j.`colorname`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %garage_brand% b ON b.`shopid` = j.`shopid` AND b.`brandid` = j.`brandid`
			LEFT JOIN %garage_insurer% c USING(`insurerid`)
		%WHERE%
		GROUP BY j.`tpid`
		ORDER BY j.`datetoreturn` ASC;
		-- {sum:"totalPrice,replyPrice"}
		';

	$dbs = mydb::select($stmt);
	//debugMsg(mydb()->_query);



	// View Model
	$toolbar = new Toolbar($self,'รายงานวันนัดรับรถ');

	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/jobcardate'),'','reportform __report_jobcarin -inlineitem');
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
			'label'=>'วันที่นัดรับรถ',
			'type'=>'text',
			'class'=>'sg-datepicker',
			'autocomplete' => 'off',
			'value'=>$getFromDate,
		)
	);
	$form->addField(
		'to',
		array(
			'label'=>'-',
			'type'=>'text',
			'class'=>'sg-datepicker',
			'autocomplete' => 'off',
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

	$form->addField(
		'st',
		array(
			'type' => 'checkbox',
			'options' => array('No' => 'เฉพาะรถยังไม่คืน'),
			'value' => post('st'),
		)
	);

	$form->addField('go',array('type'=>'button','value'=>'<span>ดูรายงาน</span>'));
	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');

	$toolbar->addNav('main', $form);

	$tables = new Table();
	$tables->thead=array('date -rcvdate'=>'วันที่นัดรับรถ','ใบสั่งซ่อม','ทะเบียน','detail -hover-parent' => 'รายละเอียดรถ');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->datetoreturn,'d/m/ปปปป'),
			$rs->jobno,
			$rs->plate,
			$rs->brandname
			. ($rs->modelname ? ' '.$rs->modelname : '')
			. ($rs->colorname ? ' / '.$rs->colorname : '')
			.'<nav class="nav iconset -hover"><a href="'.url('garage/job/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a></nav>'
		);
	}
	$ret.=$tables->build();
	return $ret;
}
?>