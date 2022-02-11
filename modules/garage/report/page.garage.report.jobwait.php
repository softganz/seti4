<?php
/**
* Garage : Report Job Wait
* Created 2020-08-01
* Modify  2020-11-22
*
* @param Object $self
* @return String
*
* @usage garage/report/jobwait
*/

$debug = true;

function garage_report_jobwait($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');

	$getShopId = post('shop');
	$getFromDate = SG\getFirst(post('from'),date('Y-m-01'));
	$getToDate = SG\getFirst(post('to'),date('Y-m-t'));

	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}
	mydb::where('j.`carindate` IS NULL');
	mydb::where('j.`rcvdate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));

	$stmt = 'SELECT
			j.*
			, b.`brandname`
			, c.`insurername`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %garage_brand% b ON b.`shopid` = j.`shopid` AND b.`brandid` = j.`brandid`
			LEFT JOIN %garage_insurer% c USING(`insurerid`)
		%WHERE%
		GROUP BY j.`tpid`
		ORDER BY j.`rcvdate` ASC;
		-- {sum:"totalPrice,replyPrice"}
		';

	$dbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);



	// View Model
	$toolbar = new Toolbar($self,'รายงานรถรอซ่อม');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/jobwait'),'','reportform -report-jobwait -inlineitem');
	$form->addConfig('method','post');

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
			'label' => 'วันที่',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'value' => sg_date($getFromDate, 'd/m/Y'),
		)
	);

	$form->addField(
		'to',
		array(
			'label' => '-',
			'type' => 'text',
			'class' => 'sg-datepicker',
			'value' => sg_date($getToDate,'d/m/Y'),
		)
	);

	$form->addField('go',array('type'=>'button','value'=>'<span>ดูรายงาน</span>'));

	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');

	$toolbar->addNav('main', $form);

	$tables = new Table();
	$tables->thead = array(
		'date -rcvdate'=>'วันที่ใบสั่งซ่อม',
		'date -toreturn'=>'วันนัดรับรถ',
		'ใบสั่งซ่อม',
		'ทะเบียน',
		'รายละเอียดรถ',
		'icons -no-print'=>''
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->rcvdate,'d/m/ปปปป'),
			$rs->datetoreturn?sg_date($rs->datetoreturn,'d/m/ปปปป'):'',
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
	$ret.='<p>จำนวนใบสั่งซ่อม '.$dbs->_num_rows.' ใบ</p>';
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>