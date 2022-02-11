<?php
function r_garage_stock_cost_calculate_v3($stkid) {
	// Set balance for each card
	if (!$stkid) {
		mydb::query('UPDATE %garage_stocktran% SET `balanceamt`=`qty`, `balancecost`=`total` WHERE `qty`>0');
		mydb::query('UPDATE %garage_stocktran% SET `price`=0,`total`=0 WHERE `qty`<=0');
	} else {
		mydb::query('UPDATE %garage_stocktran% SET `balanceamt`=`qty`, `balancecost`=`total` WHERE `qty`>0 AND `stkid`=:stkid',':stkid',$stkid, ':uid',i()->uid);
		mydb::query('UPDATE %garage_stocktran% SET `price`=0,`total`=0 WHERE `qty`<=0 AND `stkid`=:stkid',':stkid',$stkid, ':uid',i()->uid);
	}

	$ret.='<h2>Calculate Stock Balance And Cost @'.date('Y-m-d H:i:s').'</h2>';
	$ret.='<a href="'.url('garage/stock/repairall').'">Repair All Stock</a>';
	if ($stkid) $ret.=' | <a href="'.url('garage/stock/'.$stkid.'/repair').'">Repair Stock '.$stkid.'</a>';


	if ($stkid) {
		mydb::where('`stkid`=:stkid ',':stkid',$stkid);
	}
	$stmt='SELECT DISTINCT `stklocid`, `stkid`
				FROM %garage_stocktran%
				%WHERE%
				ORDER BY `stkid` ASC;';
	$dbs=mydb::select($stmt);


	$tables = new Table();
	$tables->addClass('-center');
	$tables->addConfig('showHeader',false);

	// Each Stock ID
	foreach ($dbs->items as $stockID) {
		$tables->rows[$stockID->stklocid.$stockID->stkid.'-h1']=array('<td colspan="15" style="text-align:left;"><h2>Calculate Shop = '.$stockID->stklocid.' Stock ID = '.$stockID->stkid.'</h2></td>');
		$tables->rows[$stockID->stklocid.$stockID->stkid.'-h2']='<header>';

		// Get Stock Transaction
		$stmt='SELECT tr.*, FROM_UNIXTIME(tr.`created`,"%Y-%m-%d %H:%i%s") `created`
					FROM %garage_stocktran% tr
					WHERE `stklocid`=:stklocid AND `stkid`=:stkid
					ORDER BY
						`stkdate` ASC,
						CASE
							WHEN `qty`>0 THEN 0
							WHEN `qty`<=0 THEN 1
						END ASC,
						`stktrid` ASC;
					-- {key:"stktrid"}';
		$stkidDbs=mydb::select($stmt, ':stklocid',$stockID->stklocid, ':stkid',$stockID->stkid);
		//$ret.=mydb()->_query;

		unset($tables->thead);
		foreach (reset($stkidDbs->items) as $key => $value) {
			$tables->thead[]=$key;
		}

		// First loop : find qty < 0
		foreach ($stkidDbs->items as $stockOut) {
			$tables->rows[$stockOut->stktrid]=$stockOut;

			// Stock In
			if ($stockOut->qty>=0) continue;


			// Stock Out
			$msg='Cut stock id <b>'.$stockOut->stkid.'</b> transaction no <b>'.$stockOut->stktrid.'</b> amount <b>'.$stockOut->qty.'</b> items.<br />';

			$allBalance=abs($stockOut->qty);
			$cost=$costDiscount=$costVat=0;

			// Second loop to cut in stock
			foreach ($stkidDbs->items as $key => $value) {
				if ($allBalance==0) {
					break;
				} else if ($value->balanceamt<=0) {
					continue;
				} else if ($value->balanceamt>=$allBalance) {
					// Cut some amount of stock item and still remain.
					$msg.='Case 1 :: Stock item '.$value->stktrid.' remained <b>'.$value->balanceamt.'</b> pcs. and want to cut <b>-'.$allBalance.'</b> => ';
					$costUnitPrice=$value->total/$value->qty;
					$costUnitDiscountPrice=$value->discountamt/$value->qty;
					$costUnitVatPrice=$value->vatamt/$value->qty;

					$costDiscount-=$costUnitDiscountPrice*$allBalance;
					$costVat+=($costUnitVatPrice)*$allBalance;
					$cost-=$costUnitPrice*$allBalance;

					$value->balanceamt-=$allBalance;
					$value->balancecost=$value->balanceamt*$costUnitPrice;
					// Update stock in item
					$stmt='UPDATE %garage_stocktran% SET `balanceamt`=:balanceamt, `balancecost`=:balancecost WHERE `stktrid`=:stktrid';
					mydb::query($stmt,':stktrid',$value->stktrid,':balanceamt',$value->balanceamt,':balancecost',$value->balancecost);
					//$msg.='<br />'.mydb()->_query.'<br />';

					$value->balanceamt=number_format($value->balanceamt,2,'.','');
					$allBalance=0;
				} else {
					// Cut all amount of stock item and balance is 0.
					$msg.='Case 2 :: Stock item '.$value->stktrid.' remained <b>'.$value->balanceamt.'</b> pcs. and want to cut <b>-'.$allBalance.'</b> => ';
					$costUnitPrice=$value->total/$value->qty;
					$costUnitDiscountPrice=$value->discountamt/$value->qty;
					$costUnitVatPrice=$value->vatamt/$value->qty;
					$balance=$value->balanceamt;
					$cost-=$costUnitPrice*$balance;
					$costDiscount-=$costUnitDiscountPrice*$balance;
					$costVat+=$costUnitVatPrice*$balance;
					$value->balanceamt=$value->balancecost=$stkidDbs->items[$key]->balanceamt='0.00';
					$allBalance-=$balance;
					// Update stock in item
					$stmt='UPDATE %garage_stocktran% SET `balanceamt`=0, `balancecost`=0 WHERE `stktrid`=:stktrid';
					mydb::query($stmt,':stktrid',$value->stktrid);
					//$msg.=mydb()->_query.'<br />';
				}


				// Update stock out cost transaction
				$msg.='Cut stock item '.$value->stktrid.' remained <b>'.$stkidDbs->items[$key]->balanceamt.'</b> pcs. and cost <b>'.$cost.'$</b> discount <b>'.$costDiscount.'</b> vat <b>'.$costVat.'</b> and still to cut <b>'.$allBalance.'</b> pcs.<br />';

				$stkidDbs->items[$stockOut->stktrid]->total=number_format($cost,2,'.','');
				$stkidDbs->items[$stockOut->stktrid]->price=number_format(abs($cost)/$stockOut->qty,2,'.','');
				$stkidDbs->items[$stockOut->stktrid]->discountamt=number_format($costDiscount,2,'.','');
				$stkidDbs->items[$stockOut->stktrid]->vatamt=number_format($costVat,2,'.','');

				$stmt='UPDATE %garage_stocktran% SET `total`=:cost, `discountamt`=:discountamt, `vatamt`=:vatamt WHERE `stktrid`=:stktrid LIMIT 1';
				mydb::query($stmt,':stktrid',$stockOut->stktrid,':cost',$cost, ':discountamt',$costDiscount, ':vatamt',$costVat);
				$msg.='Update stock out : '.mydb()->_query.'<br />';
			}
			$tables->rows[$stockOut->stktrid.'-msg']=array('<td colspan="15" style="text-align:left;">'.$msg.'</td>');
		}
		$tables->rows[$value->stktrid]=$value;
		//$tables->rows[$stkidDbs->items[$key]]=$stkidDbs->items;



		$stmt='UPDATE %garage_stocktran% SET `price`=ABS(`total`)/`qty` WHERE `stkid`=:stkid AND `qty`<0';
		mydb::query($stmt,':stkid',$stockOut->stkid);
		$ret.='<br />Update Stock Transaction :: '.mydb()->_query.'<br />';



		// Update Stock ID balance amount and cost
		$stmt='UPDATE %garage_repaircode% a
						LEFT JOIN
						(SELECT
							s.`stkid`
							, SUM(s.`qty`) `balanceamt`
							, SUM(s.`total`) `balancecost`
							FROM %garage_stocktran% s
							WHERE s.`stkid`=:stkid
						) b
						ON b.`stkid`=a.`repairid`
						SET
						  a.`balanceamt`=b.`balanceamt`
						, a.`balancecost`=b.`balancecost`
						WHERE a.`repairid`=:stkid
						';
		mydb::query($stmt,':stkid',$stockOut->stkid);
		$ret.='Update Stock Code :: '.mydb()->_query.'<br />';

	}
	$ret.=$tables->build();
	//$ret.=print_o($tables,'$tables');
	return $ret;
}
?>