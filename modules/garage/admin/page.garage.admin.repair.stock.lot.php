<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

function garage_admin_repair_stock_lot($self) {

	$stmt = 'SELECT t.`stktrid`, t.`lotid`,t.`price`,t.`total`,l.`stktrid` `l_stktrid`,l.`price` `l_price`,l.`total` `l_total`, l.`balanceamt` `l_balanceamt`, l.`balancecost` `l_balancecost` FROM `sgz_garage_stocktran` t
		LEFT JOIN `sgz_garage_stocktran` l ON l.`stktrid` = t.`lotid`
		WHERE t.`lotid` IS NOT NULL AND l.`stktrid` IS NOT NULL
		ORDER BY t.`stktrid` DESC LIMIT 1000';

	$lotDbs = mydb::select($stmt);

	if (SG\confirm()) {
		$stmt = 'UPDATE `sgz_garage_stocktran` t
			LEFT JOIN `sgz_garage_stocktran` l ON l.`stktrid` = t.`lotid`
			SET t.`price` = l.`price`, t.`total` = l.`total`, l.`balanceamt` = 0, l.`balancecost` = 0
			WHERE t.`lotid` IS NOT NULL AND l.`stktrid` IS NOT NULL';

		mydb::query($stmt);
	}



	// View Model
	$ret = '';
	new Toolbar($self, 'Garage Admin :: Repair Stock Lot');

	$ret .= '<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('garage/admin/repair/stock/lot').'" data-title="REPAIR" data-confirm="กรุณายืนยัน?">START REPAIR</a></nav>';
	$ret .= mydb::printtable($lotDbs);

	return $ret;
}
?>