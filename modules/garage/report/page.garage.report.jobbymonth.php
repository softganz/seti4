<?php
/**
* Garager :: Report Of Job By Month
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @return String
*
* @usage garage/report/jobbymonth
*/

$debug = true;

function garage_report_jobbymonth($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');

	$getShopId = post('shop');

	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}

	$stmt = 'SELECT
			DATE_FORMAT(`rcvdate`,"%Y-%m") `rcvMonth`
		, COUNT(*) `totalJob`
		, COUNT(IF(`iscarreturned`="Yes",1,NULL)) `totalReturn`
		, COUNT(IF(`iscarreturned`="No",1,NULL)) `totalNotReturn`
		, COUNT(IF(`isrecieved`="Yes",1,NULL)) `totalRecieve`
		, COUNT(IF(`isrecieved`="No",1,NULL)) `totalNotRecieve`
		, COUNT(IF(`isjobclosed`="Yes",1,NULL)) `totalClose`
		, COUNT(IF(`isjobclosed`="No",1,NULL)) `totalNotClose`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
		%WHERE%
		GROUP BY `rcvMonth`
		ORDER BY `rcvMonth` ASC
		';

	$reportDbs = mydb::select($stmt,':shopid',$shopInfo->shopid);

	//debugMsg(mydb()->_query);


	// View Model
	$toolbar = new Toolbar($self,'รายงานจำนวนใบสั่งซ่อมประจำเดือน');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/jobbymonth'),'','reportform __report_jobcarin -inlineitem');
	$form->addConfig('method','get');

	$form->addField(
		'shop',
		array(
			'type' => 'select',
			'options' => array('' => '==ทุกสาขา==') + R::Model('garage.shop.branch', $shopInfo->shopId, '{result: "option", value: "shortName"}'),
			'value' => $getShopId,
		)
	);

	$form->addField('go',array('type'=>'button','value'=>'<span>ดูรายงาน</span>'));

	$toolbar->addNav('main', $form);

	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead=array('เดือน-ปี','จำนวนใบสั่งซ่อม','ตกลงราคา','ไม่ตกลงราคา','ส่งรถคืน','ไม่ส่งรถคืน','ปิดใบสั่งซ่อม','ไม่ปิดใบสั่งซ่อม');

	$chart = new Table();
	foreach ($reportDbs->items as $rs) {
		$tables->rows[]=array(
			'<a href="'.url('garage/job',array('month'=>$rs->rcvMonth,'show'=>'all')).'">'.sg_date($rs->rcvMonth.'-01','m/ปปปป').'</a>',
			'<a href="'.url('garage/job',array('month'=>$rs->rcvMonth,'show'=>'all')).'">'.$rs->totalJob.'</a>',
			'<a href="'.url('garage/job',array('month'=>$rs->rcvMonth,'show'=>'returned')).'">'.$rs->totalReturn.'</a>',
			'<a href="'.url('garage/job',array('month'=>$rs->rcvMonth,'show'=>'notreturned')).'">'.$rs->totalNotReturn.'</a>',
			'<a href="'.url('garage/job',array('month'=>$rs->rcvMonth,'show'=>'recieved')).'">'.$rs->totalRecieve.'</a>',
			'<a href="'.url('garage/job',array('month'=>$rs->rcvMonth,'show'=>'notrecieved')).'">'.$rs->totalNotRecieve.'</a>',
			'<a href="'.url('garage/job',array('month'=>$rs->rcvMonth,'show'=>'closed')).'">'.$rs->totalClose.'</a>',
			'<a href="'.url('garage/job',array('month'=>$rs->rcvMonth,'show'=>'notclosed')).'">'.$rs->totalNotClose.'</a>',
		);

		$chart->rows[]=array(
			'string:Month'=>$rs->rcvMonth,
			'number:ใบสั่งซ่อม'=>number_format($rs->totalJob),
			'string:ใบสั่งซ่อม:role'=>number_format($rs->totalJob),
			'number:ส่งรถคืน'=>number_format($rs->totalReturn),
			'string:ส่งรถคืน:role'=>number_format($rs->totalReturn),
			'number:รับเงิน'=>number_format($rs->totalRecieve),
			'string:รับเงิน:role'=>number_format($rs->totalRecieve),
			'number:ปิดใบสั่งซ่อม'=>number_format($rs->totalClose),
			'string:ปิดใบสั่งซ่อม:role'=>number_format($rs->totalClose),
		);
	}
	$ret.='<div id="chart" class="sg-chart -job" data-type="col" style="height:400px;"><h3>Job</h3>'.$chart->build().'</div>';

	$ret.=$tables->build();
	//$ret.=print_o($reportDbs,'$reportDbs');
	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
	return $ret;
}
?>