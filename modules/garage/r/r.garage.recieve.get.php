<?php
/**
* Garage :: Get Recieve Information
* Created 2018-10-17
* Modify  2020-02-19
*
* @param Int $shopid
* @param Int $rcvid
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_garage_recieve_get($shopid, $rcvid, $options = '{}') {
	$defaults = '{debug:false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;
	
	$result = NULL;
	
	$stmt = 'SELECT
		  r.*
		, s.`shortname` `shopShortName`
		, i.`insurername`
		, i.`insurerphone`
		, i.`insurername`
		, i.`insurerweb`
		, i.`remark` `insurerremark`
		FROM %garage_rcv% r
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
		WHERE (r.`shopid` = :shopid OR s.`shopparent` = :shopid) AND r.`rcvid` = :rcvid
		LIMIT 1';

	$result = mydb::select($stmt,':shopid',$shopid, ':rcvid',$rcvid);

	if ($result->_num_rows) {
		if (!$debug) mydb::clearprop($result);

		$stmt = 'SELECT
			  q.`qtid`, q.`qtno`, q.`replyprice`
			, 1 `qty`, q.`rcvprice` `unitprice`, q.`rcvvat` `vat`
			, q.`rcvmoney`, q.`rcvmdate`
			, q.`insuclaimcode`
			, j.`tpid`, j.`jobno`, j.`plate`, j.`brandid`
			, IF(q.`insuno` IS NULL OR q.`insuno` = "",j.`insuno`, q.`insuno`) `insuno`
			, q.`insuclaimcode`
			FROM %garage_qt% q
				LEFT JOIN %garage_job% j USING(`tpid`)
			WHERE q.`rcvid` = :rcvid
			ORDER BY `qtid` ASC;
			-- {key:"qtid"}';

		$result->qt = mydb::select($stmt,':rcvid',$rcvid)->items;

		/*
		$vatCase = 1;
		$vatRate = $result->vatrate;
		*/

		foreach ($result->qt as $item) {
			/*
			switch ($vatCase) {
				case 2:
					// Case 2 : Price exclude vat
					$unitVat = round($item->replyprice * $vatRate / (100 + $vatRate),2);
					$unitPrice = $item->replyprice - $unitVat;
					break;
				
				default:
					// Case 1 and Other : Price include vat
					$unitPrice = round($item->replyprice / (1 + $vatRate / 100),2);
					$unitVat = $item->replyprice - $unitPrice;
					break;
			}

			$result->qt[$item->qtid]->unitprice = $unitPrice / $item->qty;
			$result->qt[$item->qtid]->vat = $unitVat / $item->qty;

			$subTotal += $itemTotal;
			$vatTotal += $unitVat;
			*/

			$grandTotal += $item->replyprice;
		}
	}
	if ($debug) debugMsg($result,'$result');
	return $result;
}
?>