<?php
/**
* Garage :: Report Of AP Master Not Pay Money
* Created 2021-01-01
* Modify  2021-04-01
*
* @param Object $self
* @return String
*
* @usage garage/report/apmastnotmoney
*/

$debug = true;

function garage_report_apmastnotmoney($self) {
	// Data Model
	$shopInfo = R::Model('garage.get.shop');
	$shopid = $shopInfo->shopid;
	$shopbranch = array_keys(R::Model('garage.shop.branch',$shopid));

	$getShopId = post('shop');
	$getFromDate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$getToDate = post('to')?sg_date(post('to'),'d/m/Y'):date('t/m/Y');

	$apList = array();
	foreach (
		mydb::select(
			'SELECT `apid`,`apname` FROM %garage_ap% WHERE `shopid`  IN (:shopbranch) ORDER BY CONVERT(`apname` USING tis620)',
			':shopbranch', 'SET:'.implode(',',$shopbranch)
		)->items as $rs) {
		$apList[$rs->apid] = $rs->apname;
	}

	if ($getShopId) {
		mydb::where('ap.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(ap.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}
	mydb::where('ap.`shopid`=:shopid',':shopid',$shopInfo->shopid);
	mydb::where('ap.`ispaid`>=0 AND ap.`paidid` IS NULL');
	mydb::where('ap.`rcvdate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));
	if (post('apid')) mydb::where('ap.`apid`=:apid',':apid',post('apid'));

	$stmt = 'SELECT
			ap.*
			, a.`apname`
		FROM %garage_apmast% ap
			LEFT JOIN %garage_shop% s ON s.`shopid` = ap.`shopid`
			LEFT JOIN %garage_ap% a USING(`apid`)
		%WHERE%
		ORDER BY ap.`rcvdate` ASC;
		-- {sum:"grandtotal"}
		';

	$reportDbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);


	// View Model
	$toolbar = new Toolbar($self,'รายงานใบรับของค้างจ่าย');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/apmastnotmoney'),'','reportform __report_jobcarin -inlineitem');
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
			'label'=>'วันที่รับของ',
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
		'apid',
		array(
			'type'=>'select',
			'style' => 'width: 120px;',
			'options'=>array(''=>'==ทุกเจ้าหนี้==')+$apList,
			'value'=>post('apid'),
		)
	);

	$form->addField('go',array('type'=>'button','value'=>'<i class="icon -search -white"></i><span>ดูรายงาน</span>'));

	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');

	$toolbar->addNav('main', $form);

	$tables = new Table();

	$tables->thead = array(
		'ใบรับของ',
		'rcvdate -date' => 'วันที่รับของ',
		'เลขที่อ้างอิง	',
		'ap '.(post('apid') ? '-no-print' : '') => 'เจ้าหนี้',
		'money -replyprice -hover-parent'=>'จำนวนเงินคงค้าง',
	);

	foreach ($reportDbs->items as $rs) {
		$tables->rows[] = array(
			$rs->rcvno,
			sg_date($rs->rcvdate,'d/m/Y'),
			$rs->refno,
			$rs->apname,
			number_format($rs->grandtotal,2)
			.'<nav class="nav iconset -hover"><a href="'.url('garage/aprcv/'.$rs->rcvid.'/view').'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a></nav>'
		);
	}

	$tables->tfoot[] = array(
		'',
		'',
		'',
		'รวม '.$reportDbs->_num_rows.' ใบสั่งซ่อม',
		number_format($reportDbs->sum->grandtotal,2),
	);

	$ret .= $tables->build();

	return $ret;
}
?>