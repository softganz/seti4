<?php
/**
* Garage :: Job Recieved Money Report
* Created 2019-02-28
* Modify  2021-04-01
*
* @param Object $self
* @return String
*
* @usage garage/report/jobrcvmoney
*/

$debug = true;

function garage_report_jobrcvmoney($self) {
	// Data Model
	$getShopId = post('shop');
	$getInsurer = post('insurer');
	$getFromDate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$getToDate = post('to')?sg_date(post('to'),'d/m/Y'):date('t/m/Y');

	$shopInfo = R::Model('garage.get.shop');

	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}
	mydb::where('q.`rcvmdate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));
	if ($getInsurer) mydb::where('j.`insurerid` = :insurerid',':insurerid',$getInsurer);
	$stmt='SELECT
			q.`tpid`, q.`rcvmoney`, q.`rcvmdate`
			, j.`jobno`, j.`plate`
			, c.`insurername`
			, (SELECT SUM(`replyprice`) FROM %garage_qt% q WHERE q.`tpid`=j.`tpid`) `replyPrice`
		FROM %garage_qt% q
			LEFT JOIN %garage_job% j USING(`tpid`)
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %garage_insurer% c ON c.`insurerid` = q.`insurerid`
		%WHERE%
		ORDER BY `rcvmdate` ASC;
		-- {sum:"replyPrice,rcvmoney"}
			';
	$reportDbs = mydb::select($stmt);
	
	//debugMsg(mydb()->_query);



	// View Model
	$toolbar = new Toolbar($self,'รายงานการรับเงิน');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/jobrcvmoney'),'','reportform __report_jobcarin -inlineitem');
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
			'label'=>'วันที่รับเงิน',
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

	$form->addField('go',array('type'=>'button','value'=>'<i class="icon -search -white"></i><span class="-hidden">ดูรายงาน</span>'));

	$form->addField('print', array('type'=>'textfield','value'=>'<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>'));

	$toolbar->addNav('main', $form);

	$tables = new Table();
	$tables->thead = array(
		'job -nowrap'=>'ใบสั่งซ่อม',
		'ทะเบียน',
		'insu' => 'บริษัทประกัน',
		'money -rcv'=>'รับเงิน',
		'date -rcvmoneydate -hover-parent'=>'วันที่รับ'
	);

	if ($getInsurer) unset($tables->thead['insu']);

	foreach ($reportDbs->items as $rs) {
		unset($row);
		$row = array(
			$rs->jobno,
			$rs->plate,
			'insu' => $rs->insurername,
			number_format($rs->rcvmoney,2),
			($rs->rcvmdate ? sg_date($rs->rcvmdate,'d/m/ปปปป') : '')
			.'<nav class="nav iconset -hover"><a href="'.url('garage/job/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a></nav>'
		);
		if ($getInsurer) unset($row['insu']);
		$tables->rows[] = $row;
	}
	$tables->tfoot[]=array(
		'<td colspan="'.($getInsurer ? 2 : 3).'">จำนวนใบสั่งซ่อม '.$reportDbs->_num_rows.' รายการ</td>',
		number_format($reportDbs->sum->rcvmoney,2),
		'',
	);
	$ret.=$tables->build();
	return $ret;
}
?>