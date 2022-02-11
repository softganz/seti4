<?php
/**
* Garage :: Report Of Wage
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @return String
*
* @usage garage/report/wage
*/

$debug = true;

function garage_report_wage($self) {
	$shopInfo = R::Model('garage.get.shop');

	$getShopId = post('shop');
	$getFromDate = post('from') ? sg_date(post('from'),'d/m/Y') : date('01/m/Y');
	$getToDate = post('to')?sg_date(post('to'),'d/m/Y'):date('t/m/Y');

	if ($getShopId) {
		mydb::where('j.`shopid` = :shopId', ':shopId', $getShopId);
	} else {
		mydb::where('(j.`shopid` = :shopId OR s.`shopparent` = :shopId)', ':shopId', $shopInfo->shopId);
	}
	mydb::where('j.`shopid` = :shopid',':shopid',$shopInfo->shopid);
	mydb::where('c.`repairtype` = 3 AND w.`datecmd` BETWEEN :fromdate AND :todate',':fromdate',sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));

	$stmt = 'SELECT
			w.*
		, j.`jobno`
		, c.`repairname`
		FROM %garage_jobtr% w
			LEFT JOIN %garage_job% j USING(`tpid`)
			LEFT JOIN %garage_shop% s ON s.`shopid` = j.`shopid`
			LEFT JOIN %garage_repaircode% c USING(`repairid`)
		%WHERE%
		ORDER BY w.`repairid`, w.`datecmd` ASC;
		';

	$reportDbs = mydb::select($stmt);

	//debugmsg(mydb()->_query);


	// View Model
	$toolbar = new Toolbar($self,'รายงานค่าแรง');
	$self->theme->sidebar = R::View('garage.report.menu');

	$form = new Form(NULL,url('garage/report/wage'),'','reportform -wage -inlineitem');
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
	$form->addField('go',array('type'=>'button','value'=>'<i class="icon -search -white"></i>'));
	$form->addField('print','<a class="btn" href="javascript:window.print()"><i class="icon -print"></i></a>');

	$toolbar->addNav('main', $form);

	$tables = new Table();

	$tables->thead = array(
		'date -rcvdate'=>'วันที่',
		'ใบสั่งซ่อม',
		'qty -amt'=>'จำนวน',
		'unitpr -money'=>'ราคา',
		'totalpr -money'=>'รวมเงิน',
		'icons -no-print'=>''
	);

	$tables->addConfig('showHeader',false);

	$curRepairId = NULL;
	$curQty = $curSum = 0;

	foreach ($reportDbs->items as $rs) {
		if ($curRepairId != $rs->repairid) {
			if ($curSum != 0) {
				$tables->rows[] = array('','<b>รวม</b>','<b>'.number_format($curQty,2).'</b>','','<b>'.number_format($curSum,2).'</b>');
				$tables->rows[] = '<tr><td colspan="6">&nbsp;</td></tr>';
				$curQty = $curSum = 0;
			}
			$tables->rows[] = array('<td colspan="6" class="subheader">'.$rs->repairname.'</td>');
			$tables->rows[] = '<header>';
			$curRepairId = $rs->repairid;
		}
		$tables->rows[]=array(
			sg_date($rs->datecmd,'d/m/ปปปป'),
			$rs->jobno,
			number_format($rs->qty,2),
			number_format($rs->price,2),
			number_format($rs->totalsale,2),
			'<a href="'.url('garage/job/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>'
		);
		$curQty += $rs->qty;
		$curSum += $rs->totalsale;
	}
	if ($curSum != 0) {
		$tables->rows[] = array('','<b>รวม</b>','<b>'.number_format($curQty,2).'</b>','','<b>'.number_format($curSum,2).'</b>');
		$curQty = $curSum = 0;
	}

	$ret .= $tables->build();

	//$ret.=print_o($reportDbs,'$reportDbs');
	return $ret;
}
?>