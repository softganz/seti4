<?php
/**
* Garage :: Report of Car Date To Return
* Created 2018-01-25
* Modify  2021-03-12
*
* @param Object $self
* @return String
*
* @usage garage/report/datetoreturn
*/

$debug = true;

function garage_report_datetoreturn($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');
	$fromdate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$todate = post('to') ? sg_date(post('to'),'d/m/Y') : date('t/m/Y');
	$getShopId = post('shop');

	mydb::where('j.`iscarreturned`!="Yes"');
	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}

	$stmt = 'SELECT
			j.*
			, b.`brandname`
			, c.`customername`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %garage_brand% b ON b.`shopid` = j.`shopid` AND b.`brandid` = j.`brandid`
			LEFT JOIN %garage_customer% c USING(`customerid`)
		%WHERE%
		GROUP BY j.`tpid`
		ORDER BY j.`datetoreturn` ASC
		';

	$jobDbs = mydb::select($stmt);



	// View Model
	$toolbar = new Toolbar($self,'รายงานวันที่นัดรับรถ');

	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/datetoreturn'),'','-inlineitem');
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
			'label' => 'วันที่นัด',
			'type' => 'text',
			'class' => 'sg-datepicker -date',
			'size' => 10,
			'value' => $fromdate,
		)
	);

	$form->addField(
		'to',
		array(
			'label'=>'-',
			'type'=>'text',
			'class'=>'sg-datepicker -date',
			'size' => 10,
			'value'=>$todate,
		)
	);
	*/

	$form->addField('go',array('type'=>'button','value'=>'<span>ดูรายงาน</span>'));
	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');

	$toolbar->addNav('main', $form);

	$tables = new Table();
	$tables->thead=array('date -rcvdate'=>'วันที่นัดรับรถ','เวลา','ใบสั่งซ่อม','ทะเบียน','รายละเอียดรถ','ลูกค้า','สถานะ','');
	$curdate='';

	foreach ($jobDbs->items as $rs) {
		if ($curdate!=$rs->datetoreturn) {
			$tables->rows[]=array('<td colspan="8">'.($rs->datetoreturn?sg_date($rs->datetoreturn,'d/m/ปปปป'):'').'</td>','config'=>array('class'=>'subheader'));
			$curdate=$rs->datetoreturn;
		}
		$tables->rows[]=array(
			$rs->datetoreturn?sg_date($rs->datetoreturn,'d/m/ปปปป'):'',
			$rs->datetoreturn && $rs->timetoreturn?' '.substr($rs->timetoreturn,0,5):'',
			$rs->jobno,
			$rs->plate,
			$rs->brandname.' '.$rs->modelname.($rs->colorname?' สี'.$rs->colorname:''),
			$rs->customername,
			GarageVar::$jobStatusList[$rs->jobstatus],
			'<a href="'.url('garage/job/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>'
			);
	}

	$ret.=$tables->build();
	$ret.='<p>จำนวนรถคงเหลือในอู่ '.$dbs->_num_rows.' คัน</p>';
	return $ret;
}
?>