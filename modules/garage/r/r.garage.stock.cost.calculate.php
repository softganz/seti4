<?php
/**
* Calculate Stock Cost
* Created 2019-03-07
* Modify  2019-11-19
*
* @param Int $stockId
* @param Object $options
* @return String
*/

$debug = true;

function r_garage_stock_cost_calculate($stockId = NULL, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	// Set balance for each card
	if ($stockId) {
		mydb::query(
			'UPDATE %garage_stocktran% SET `balanceamt`=`qty`, `balancecost` = `total` WHERE `qty` > 0 AND `stkid` = :stkid',
			':stkid', $stockId
		);
		mydb::query(
			'UPDATE %garage_stocktran% SET `price` = 0,`total` = 0 WHERE `qty` <= 0 AND `stkid` = :stkid',
			':stkid', $stockId
		);
	} else {
		mydb::query('UPDATE %garage_stocktran% SET `balanceamt` = `qty`, `balancecost` = `total` WHERE `stkid` IS NOT NULL AND `qty` > 0');
		mydb::query('UPDATE %garage_stocktran% SET `price` = 0,`total` = 0 WHERE `stkid` IS NOT NULL AND `qty` <= 0');
	}

	$ret .= '<h2>Calculate Stock Balance And Cost @'.date('Y-m-d H:i:s').'</h2>';
	$ret .= '<a href="'.url('garage/stock/repairall').'">Repair All Stock</a>';
	if ($stockId) $ret.=' | <a href="'.url('garage/stock/'.$stockId.'/repair').'">Repair Stock '.$stockId.'</a>';


	mydb::where('`stkid` IS NOT NULL');

	if ($stockId) mydb::where('`stkid` = :stkid ',':stkid',$stockId);

	$stmt = 'SELECT DISTINCT `stklocid`, `stkid`
		FROM %garage_stocktran%
		%WHERE%
		ORDER BY `stkid` ASC;';

	$stockCodeDbs = mydb::select($stmt);


	$tables = new Table();
	$tables->addClass('-center');
	$tables->addConfig('showHeader',false);

	// Each Stock ID
	foreach ($stockCodeDbs->items as $stockID) {
		if (empty($stockID->stkid)) continue;

		$tables->rows[$stockID->stklocid.$stockID->stkid.'-h1']=array('<td colspan="15" style="text-align:left;"><h2>Calculate Shop = '.$stockID->stklocid.' Stock ID = '.$stockID->stkid.'</h2></td>');
		//$tables->rows[$stockID->stklocid.$stockID->stkid.'-h2']='<header>';

		// Get Stock Transaction
		$stmt = 'SELECT
			tr.*,
			FROM_UNIXTIME(tr.`created`,"%Y-%m-%d %H:%i%s") `created`
			FROM %garage_stocktran% tr
			WHERE `stklocid` = :stklocid AND `stkid` = :stkid
			ORDER BY
				`stkdate` ASC,
				CASE
					WHEN `qty` > 0 THEN 0
					WHEN `qty` <= 0 THEN 1
				END ASC,
				`stktrid` ASC;
			-- {key:"stktrid"}';

		$stockTranDbs = mydb::select($stmt, ':stklocid',$stockID->stklocid, ':stkid',$stockID->stkid);

		//$ret.=mydb()->_query;

		unset($tables->thead);
		foreach (reset($stockTranDbs->items) as $key => $value) {
			$tables->thead[]=$key;
		}

		// First loop : find qty < 0
		foreach ($stockTranDbs->items as $stockOut) {
			$tables->rows[$stockID->stklocid.$stockID->stkid.(++$no)]='<header>';
			$tables->rows[$stockOut->stktrid]=$stockOut;

			// Stock In
			if ($stockOut->qty >= 0) continue;


			// Stock Out
			$msg = 'Cut stock id <b>'.$stockOut->stkid.'</b> transaction no <b>'.$stockOut->stktrid.'</b> amount <b>'.$stockOut->qty.'</b> items.<br />';

			$allBalance = abs($stockOut->qty);
			$cost = $costDiscount = $costVat = 0;

			// Second loop to cut in stock
			foreach ($stockTranDbs->items as $key => $value) {
				if ($allBalance == 0) {
					break;
				} else if ($value->balanceamt <= 0) {
					continue;
				} else if ($value->balanceamt >= $allBalance) {
					// Cut some amount of stock item and still remain.
					$msg .= 'Case 1 :: Stock item '.$value->stktrid.' remained <b>'.$value->balanceamt.'</b> pcs. and want to cut <b>-'.$allBalance.'</b> => ';
					$costUnitPrice = $value->total / $value->qty;
					$costUnitDiscountPrice = $value->discountamt / $value->qty;
					$costUnitVatPrice = $value->vatamt / $value->qty;

					$costDiscount -= $costUnitDiscountPrice * $allBalance;
					$costVat += ($costUnitVatPrice) * $allBalance;
					$cost -= $costUnitPrice * $allBalance;
					$cost = round($cost,2);

					$value->balanceamt -= $allBalance;
					$value->balanceamt = round($value->balanceamt,2);
					$value->balancecost = round($value->balanceamt * $costUnitPrice,2);
					// Update stock in item
					$stmt = 'UPDATE %garage_stocktran% SET `balanceamt`=:balanceamt, `balancecost`=:balancecost WHERE `stktrid`=:stktrid';
					mydb::query($stmt,':stktrid',$value->stktrid,':balanceamt',$value->balanceamt,':balancecost',$value->balancecost);
					//$msg.='<br />'.mydb()->_query.'<br />';

					$value->balanceamt = number_format($value->balanceamt,2,'.','');
					$allBalance = 0;
				} else {
					// Cut all amount of stock item and balance is 0.
					$msg .= 'Case 2 :: Stock item '.$value->stktrid.' remained <b>'.$value->balanceamt.'</b> pcs. and want to cut <b>-'.$allBalance.'</b> => ';
					$costUnitPrice = $value->total / $value->qty;
					$costUnitDiscountPrice = $value->discountamt / $value->qty;
					$costUnitVatPrice = $value->vatamt / $value->qty;
					$balance = $value->balanceamt;
					$cost -= $costUnitPrice * $balance;
					$cost = round($cost,2);
					$costDiscount -= $costUnitDiscountPrice * $balance;
					$costVat += $costUnitVatPrice * $balance;
					$value->balanceamt = $value->balancecost = $stockTranDbs->items[$key]->balanceamt = '0.00';
					$allBalance -= $balance;
					// Update stock in item
					$stmt = 'UPDATE %garage_stocktran% SET `balanceamt`=0, `balancecost`=0 WHERE `stktrid`=:stktrid';
					mydb::query($stmt,':stktrid',$value->stktrid);
					//$msg.=mydb()->_query.'<br />';
				}


				// Update stock out cost transaction
				$msg .= 'Cut stock item '.$value->stktrid.' remained <b>'.$stockTranDbs->items[$key]->balanceamt.'</b> pcs. and cost <b>'.$cost.'$</b> discount <b>'.$costDiscount.'</b> vat <b>'.$costVat.'</b> and still to cut <b>'.$allBalance.'</b> pcs.<br />';

				$stockTranDbs->items[$stockOut->stktrid]->total=number_format($cost,2,'.','');
				$stockTranDbs->items[$stockOut->stktrid]->price=number_format(abs($cost)/$stockOut->qty,2,'.','');
				$stockTranDbs->items[$stockOut->stktrid]->discountamt=number_format($costDiscount,2,'.','');
				$stockTranDbs->items[$stockOut->stktrid]->vatamt=number_format($costVat,2,'.','');

				$stmt = 'UPDATE %garage_stocktran% SET `total` = :cost, `discountamt` = :discountamt, `vatamt` = :vatamt WHERE `stktrid` = :stktrid LIMIT 1';
				mydb::query($stmt,':stktrid',$stockOut->stktrid,':cost',$cost, ':discountamt',$costDiscount, ':vatamt',$costVat);
				$msg .= 'Update stock out : '.mydb()->_query.'<br />';
			}

			$tables->rows[$stockOut->stktrid.'-msg'] = array('<td colspan="15" style="text-align:left;">'.$msg.'</td>');

		}

		$tables->rows[$value->stktrid] = $value;
		//$tables->rows[$stockTranDbs->items[$key]]=$stockTranDbs->items;



		mydb::query(
			'UPDATE %garage_stocktran% SET `price` = ABS(`total`)/`qty` WHERE `stkid` = :stkid AND `qty` < 0',
			':stkid',$stockOut->stkid
		);

		$ret .= '<br />Update Stock Transaction :: '.mydb()->_query.'<br />';



		// Update Stock ID balance amount and cost
		$stmt = 'UPDATE %garage_repaircode% a
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

		$ret .= 'Update Stock Code :: '.mydb()->_query.'<br />';
	}

	//$ret .= $tables->build();

	//$ret .= print_o($tables,'$tables');

	return $ret;
}
?>