<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_job_cost($self, $jobInfo) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;

	new Toolbar($self,'ใบสั่งซ่อม - '.$jobInfo->plate,'job',$jobInfo);

	$isEditable = in_array($shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING'));
	$isViewable = $jobInfo->is->editable;

	if (!$isViewable) return message('error', 'Access Denied');


	$ret = '';

	$tables = new Table();
	$tables->addClass('-garage-job-tran'.($action?' -'.$action:''));
	$tables->caption = 'รายการต้นทุน';
	$tables->thead = array('date'=>'วันที่','refcode -no-print'=>'เลขที่เอกสาร','refno -no-print'=>'เลขที่อ้างอิง','stkcode -no-print'=>'รหัสอะไหล่','รายการ','amt'=>'จำนวน','money -price'=>'ราคา','money -subtotal'=>'รวม','money -discount'=>'ส่วนลด','money -vat'=>'VAT','money -total'=>'จำนวนเงิน');


	foreach ($jobInfo->cost as $rs) {
		$qty = abs($rs->qty);
		$price = abs($rs->price);
		$discountamt = abs($rs->discountamt);
		$vatamt = abs($rs->vatamt);

		$unitPrice = $price; // + ($discountamt / $qty) - ($vatamt / $qty);
		$itemSubTotal = $unitPrice * $qty;
		$itemDiscountAmt = $discountamt;
		$itemVatAmt = $vatamt;
		$itemTotal = abs($rs->total);

		$subTotal += $itemSubTotal;
		$subDiscount += $itemDiscountAmt;
		$subVat += $itemVatAmt;

		if ($rs->rcvid) {
			$refUrl = '<a href="'.url('garage/aprcv/'.$rs->rcvid.'/view').'" target="_blank">'.$rs->refcode.'</a>';
		} else if ($rs->reqid) {
			$refUrl = '<a href="'.url('garage/req/'.$rs->reqid.'/view').'" target="_blank">'.$rs->refcode.'</a>';
		} else {
			$refUrl = $rs->refcode;
		}

		$tables->rows[] = array(
			sg_date($rs->created,'d/m/ปปปป'),
			$refUrl,
			$rs->refno,
			'<a href="'.url('garage/stock/'.$rs->stkid).'" target="_blank">'.$rs->stkcode.'</a>',
			SG\getFirst($rs->description,$rs->stkname),
			$qty,
			number_format($unitPrice,2),
			number_format($itemSubTotal,2),
			$discountamt?number_format($itemDiscountAmt,2):'',
			$vatamt?number_format($itemVatAmt,2):'',
			number_format($itemTotal,2),
			'config'=>array('class'=>'item-cost'),
		);
	}

	$totalError = (String) ($subTotal - $subDiscount + $subVat) != (String) abs($jobInfo->totalCost);

	$tables->rows[] = array(
		'',
		'',
		'',
		'',
		'รวมต้นทุน',
		'',
		'',
		number_format($subTotal,2),
		number_format($subDiscount,2),
		number_format($subVat,2),
		'<span '.($totalError ? 'style="color:red" data-tooltip="totalCost='.abs($jobInfo->totalCost).' != '.($subTotal-$subDiscount+$subVat).'"' : '').'>'
		. number_format(abs($jobInfo->totalCost),2)
		. ($totalError ? '!' : '')
		. '</span>',
		'config'=>array('class'=>'item-cost subfooter')
	);

	$ret .= $tables->build();

	//$ret.=print_o($jobInfo,'$jobInfo');
	
	$ret .= '
		<style type="text/css">
		.item.-garage-job-tran .-no-print {color:#bbb;}
		.item.-garage-job-tran .-no-print a {color:#bbb;}
		@media print {
			.garage-job-view .-header {display: none;}
			.garage-job-view .card-item {width:23%; margin-bottom:0;}
			.garage-job-view>.-side>.-info>.card-item:nth-child(4),
			.garage-job-view>.-side>.-info>.card-item:nth-child(5),
			.garage-job-view>.-side>.-info>.card-item:nth-child(n+8) {display: none;}
		}
		</style>'
		;

	return $ret;
}
?>