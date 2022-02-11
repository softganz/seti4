<?php
/**
* Garage :: Report Of Recieve
* Created 2018-08-26
* Modify  2021-04-01
*
* @param Object $self
* @return String
*
* @usage garage/report/recieve
*/

$debug = true;

function garage_report_recieve($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');

	$getShopId = post('shop');
	$getInsurer = post('insurer');
	$getFromDate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$getToDate = post('to')?sg_date(post('to'),'d/m/Y'):date('t/m/Y');

	if ($getShopId) {
		mydb::where('r.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(r.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}
	mydb::where('r.`rcvdate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));
	if ($getInsurer) mydb::where('r.`insurerid` = :insurerid',':insurerid',$getInsurer);

	$stmt = 'SELECT
			r.*
			, c.`insurername`
			, SUM(q.`replyprice`) `totalRcv`
		FROM %garage_rcv% r
			LEFT JOIN %garage_shop% s ON s.`shopid` = r.`shopid`
			LEFT JOIN %garage_insurer% c USING(`insurerid`)
			LEFT JOIN %garage_qt% q USING(`rcvid`)
		%WHERE%
		GROUP BY r.`rcvid`
		ORDER BY r.`rcvid` ASC;
		-- {sum:"totalRcv"}
		';

	$reportDbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);



	// View Model
	$toolbar = new Toolbar($self,'รายงานใบเสร็จรับเงิน');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/recieve'),'','reportform -inlineitem');
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
			'label'=>'วันที่',
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
		'date'=>'วันที่',
		'เลขที่',
		'บริษัทประกัน',
		'money -price'=>'ยอดเงิน',
		'money -vat'=>'ภาษีมูลค่าเพิ่ม',
		'money -total'=>'จำนวนเงิน',
		''
	);

	$totalPrice=$totalVat=0;
	foreach ($reportDbs->items as $rs) {
		$price=round($rs->totalRcv/1.07,2);
		$vat=$rs->totalRcv-$price;
		$tables->rows[]=array(
			sg_date($rs->rcvdate,'d/m/ปปปป'),
			$rs->rcvno,
			$rs->insurername,
			number_format($price,2),
			number_format($vat,2),
			number_format($rs->totalRcv,2),
			'<a href="'.url('garage/recieve/'.$rs->rcvid).'" title="ดูรายละเอียด" target="_blank"><i class="icon -viewdoc"></i></a>'
		);

		$totalPrice+=$price;
		$totalVat+=$vat;
	}

	$tables->tfoot[]=array(
		'<td colspan="3">จำนวนใบเสร็จรับเงิน '.$reportDbs->_num_rows.' รายการ</td>',
		number_format($totalPrice,2),
		number_format($totalVat,2),
		number_format($reportDbs->sum->totalRcv,2),
		'',
		''
	);

	$ret .= $tables->build();

	return $ret;
}
?>