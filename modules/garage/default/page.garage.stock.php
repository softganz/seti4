<?php
function garage_stock($self,$stkid=NULL,$action=NULL,$trid=NULL) {
	$shopInfo = R::Model('garage.get.shop');

	new Toolbar($self,'สินค้าคงคลัง','stock',$stkid);

	if (is_numeric($stkid)) $stockInfo = R::Model('garage.stock.get',$shopInfo->shopid,$stkid);

	switch ($action) {
		case 'card':
			$ret .= __garage_stock_card($stockInfo);
			break;

		case 'sheet':
			$ret .= __garage_stock_sheet($stockInfo);
			break;

		case 'repair':
			$ret .= R::Model('garage.stock.cost.calculate',$stkid);
			break;

		case 'repairall':
			$ret .= R::Model('garage.stock.cost.calculate');
			break;
		
		default:
			if ($stkid) {
				$ret .= __garage_stock_card($stockInfo);
			} else {
				$stmt='SELECT * FROM %garage_repaircode% WHERE `repairtype`=2 ORDER BY CONVERT(`repairname` USING tis620) ASC';
				$dbs=mydb::select($stmt);

				$tables = new Table();
				$tables->thead=array('amt -id'=>'StkID','StkCode','รายการ','amt -qty'=>'จำนวนคงเหลือ','money -cost'=>'มูลค่าคงเหลือ','icons -c1'=>'');
				foreach ($dbs->items as $rs) {
					$tables->rows[]=array(
						$rs->repairid,
						$rs->repaircode,
						'<a href="'.url('garage/stock/'.$rs->repairid).'">'.$rs->repairname.'</a>',
						$rs->balanceamt,
						$rs->balancecost,
						'<a class="sg-action" href="'.url('garage/stock/'.$rs->repairid).'" data-rel="box"><i class="icon -viewdoc"></i></a>',
					);
				}
				$ret.=$tables->build();
			}
			break;
	}
	//$ret .= print_o($stockInfo, '$stockInfo');
	//$ret.=print_o($shopInfo,'$shopInfo');
	return $ret;
}

function __garage_stock_sheet($stockInfo) {
	$ret .= '<h3>ชื่ออะไหล่ : '.$stockInfo->repairname.'</h3>';

	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead = array('aid','rcvdate -date'=>'วันที่','ใบสั่งซ่อม','เลขที่เอกสาร','center -loc'=>'คลังสินค้า','จำนวน','ราคา','รวมเงิน','จำนวนล็อตคงเหลือ','ต้นทุนล็อตคงเหลือ','วันที่ทำรายการ');

	$balanceAmt = $balanceCost = 0;
	foreach ($stockInfo->items as $rs) {
		$balanceAmt += $rs->qty;
		$balanceCost += $rs->total;
		$balanceCost = round($balanceCost,2);
		$isRcv = $rs->qty > 0;
		$tables->rows[] = array(
			$rs->stktrid,
			$rs->stkdate,
			$rs->tpid?'<a href="'.url('garage/job/'.$rs->tpid).'">'.$rs->jobno:'',
			$rs->refcode,
			$rs->stklocid,
			$rs->qty,
			number_format($rs->price,2),
			$rs->total,
			$isRcv?number_format($rs->balanceamt,2):'',
			$isRcv?number_format($rs->balancecost,2):'',
			$rs->created,
		);
	}
	$tables->tfoot[] = array('','','','','','','','',number_format($stockInfo->balanceamt,2),number_format($stockInfo->balancecost,2),'');
	$ret .= $tables->build();
	return $ret;
}

function __garage_stock_card($stockInfo) {
	$ret .= '<h3>ชื่ออะไหล่ : '.$stockInfo->repairname.'</h3>';

	$tables = new Table();
	$tables->thead=array('date -doc'=>'วันที่','ใบสั่งซ่อม','เลขที่เอกสาร','center -loc'=>'คลังสินค้า','amt -amt'=>'จำนวน','money -price'=>'ราคา','money -total'=>'รวมเงิน','amt -balance'=>'คงเหลือ','money -cost'=>'มูลค่าคงเหลือ','date -created'=>'วันที่ทำรายการ');
	$balanceAmt = $balanceCost = 0;
	foreach ($stockInfo->items as $rs) {
		$balanceAmt += $rs->qty;
		$balanceCost += $rs->total;
		$balanceCost = round($balanceCost,2);
		$tables->rows[] = array(
			$rs->stkdate,
			$rs->tpid?'<a href="'.url('garage/job/'.$rs->tpid).'">'.$rs->jobno:'',
			$rs->refcode,
			$rs->stklocid,
			$rs->qty,
			number_format($rs->price,2),
			$rs->total,
			number_format($balanceAmt,2),
			number_format($balanceCost,2),
			$rs->created,
		);
	}
	$tables->tfoot[] = array('','','','','','','',number_format($stockInfo->balanceamt,2),number_format($stockInfo->balancecost,2),'');
	$ret .= $tables->build();
	return $ret;
}
?>