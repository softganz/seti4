<?php
/**
* Garage Model :: Calculate Recieve VAT
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_garage_recieve_vat_update($rcvId , $tranId = 'SUM', $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$rcvInfo = mydb::select('SELECT * FROM %garage_rcv% WHERE `rcvid` = :rcvid LIMIT 1', ':rcvid', $rcvId);

	if ($rcvInfo->_empty) return $result;

	$qtList = mydb::select('SELECT * FROM %garage_qt% WHERE `rcvid` = :rcvid', ':rcvid', $rcvId)->items;

	$vatCase = 1;

	$vatRate = $rcvInfo->vatrate;

	$subTotal = $vatTotal = $grandTotal = 0;

	foreach ($qtList as $rs) {
		$qty = 1;

		if ($tranId == 'ALL' || $rs->qtid == $tranId) {
			switch ($vatCase) {
				case 2:
					// Case 2 : Price exclude vat
					$unitVat = round($rs->replyprice * $vatRate / (100 + $vatRate),2);
					$unitPrice = $rs->replyprice - $unitVat;
					break;
				
				default:
					// Case 1 and Other : Price include vat
					$unitPrice = round($rs->replyprice / (1 + $vatRate / 100),2);
					$unitVat = $rs->replyprice - $unitPrice;
					break;
			}
			$rs->rcvprice = $unitPrice;
			$rs->rcvvat = $unitVat;
			$stmt = 'UPDATE %garage_qt% SET `rcvprice` = :rcvprice, `rcvvat` = :rcvvat WHERE `qtid` = :qtid LIMIT 1';
			mydb::query($stmt, $rs);
		}

		$itemTotal = $rs->rcvprice * $qty;
		$subTotal += $itemTotal;
		$vatTotal += $rs->rcvvat;
		$grandTotal += $rs->replyprice;
	}

	$stmt = 'UPDATE %garage_rcv% SET
		`subtotal` = :subTotal
		, `vattotal` = :vatTotal
		, `total` = :grandTotal
		WHERE `rcvid` = :rcvId
		LIMIT 1
		';

	mydb::query($stmt, ':rcvId', $rcvId, ':subTotal', $subTotal, ':vatTotal', $vatTotal, ':grandTotal', $grandTotal);

	//debugMsg(mydb()->_query);

	//debugMsg('subTotal = '.$subTotal);
	//debugMsg('vatTotal = '.$vatTotal);
	//debugMsg('grandTotal = '.$grandTotal);

	//debugMsg($rcvInfo, '$rcvInfo');
	//debugMsg($qtList, '$qtList');

	return $result;
}
?>