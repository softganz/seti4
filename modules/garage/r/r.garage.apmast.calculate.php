<?php
function r_garage_apmast_calculate($rcvInfo,$options='{}') {
	$defaults='{value:"repairname",debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	// Update subtotal and grandtotal
	$stmt='UPDATE %garage_apmast% a
					LEFT JOIN
					(SELECT
						s.`refcode`
						, SUM(s.`qty`*s.`price`) `subtotal`
						, SUM(s.`vatamt`) `vatamt`
						, SUM(s.`discountamt`) `discountamt`
						, SUM(s.`qty`*s.`price`-s.`discountamt`+s.`vatamt`) `grandtotal`
						FROM %garage_stocktran% s
						WHERE s.`shopid`=:shopid AND s.`refcode`=:refcode AND s.`qty`>0
					) b
					ON b.`refcode`=a.`rcvno`
					SET
					a.`subtotal`=b.`subtotal`
					, a.`discountamt`=b.`discountamt`
					-- , @vatamt:=(b.`subtotal`-b.`discountamt`)*a.`vatrate`/100
					, a.`vatamt`=(b.`subtotal`-b.`discountamt`)*a.`vatrate`/100
					, a.`grandtotal`=b.`subtotal`-b.`discountamt`+(b.`subtotal`-b.`discountamt`)*a.`vatrate`/100
					WHERE a.`shopid`=:shopid AND a.`rcvno`=:refcode
					';
	mydb::query($stmt, ':shopid',$rcvInfo->shopid, ':refcode',$rcvInfo->rcvno);
	if ($debug) debugMsg(mydb()->_query);
}
?>