<?php
function r_garage_order_calculate($orderId) {
	// Update subtotal and grandtotal
	$stmt = 'UPDATE %garage_ordmast% a
		LEFT JOIN
		(SELECT
			s.`ordid`
			, SUM(s.`qty`*s.`price`) `subtotal`
			, SUM(s.`qty`*s.`price`) `grandtotal`
			FROM %garage_ordtran% s
			WHERE s.`ordid` = :docid
		) b
		ON b.`ordid` = a.`ordid`
		SET
		a.`subtotal` = b.`subtotal`
		, a.`discountamt` = (b.`subtotal`)*(a.`discountrate`/100)
		, a.`vatamt` = (b.`subtotal`-a.`discountamt`)*(a.`vatrate`/100)
		, a.`total` = (b.`subtotal`-a.`discountamt`)*(1+a.`vatrate`/100)
		WHERE a.`ordid` = :docid
		';

	mydb::query($stmt, ':docid', $orderId);

	//debugMsg($orderInfo,'$orderInfo');
	//debugMsg(mydb()->_query);
}
?>