<?php
/**
* Get Garage Invoice Information
* Created 2019-10-13
* Modify  2019-10-13
*
* @param Int $shopId
* @param Int $invoiceId
* @return Object $options
*/

$debug = true;

function r_garage_invoice_get($shopId, $invoiceId, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	$rs = NULL;
	
	$stmt = 'SELECT
		  b.*
		  , i.`insurername`
		  , i.`insureraddr`
		  , i.`insurertaxid`
		FROM %garage_invoice% b
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
		WHERE (b.`shopid` = :shopid OR s.`shopparent` = :shopid ) AND b.`invoiceid` = :invoiceid
		LIMIT 1';

	$rs = mydb::select($stmt,':shopid',$shopId, ':invoiceid',$invoiceId);

	if ($rs->_num_rows) {
		if (!$debug) mydb::clearprop($rs);

		$rs->total = 0;

		$stmt = 'SELECT
			  q.*
			, j.*
			, q.`insuclaimcode`
			FROM %garage_qt% q
				LEFT JOIN %garage_job% j USING(`tpid`)
			WHERE q.`invoiceid` = :invoiceid
			ORDER BY q.`tpid` ASC;
			-- {key: "qtid"}
			';

		$rs->trans = mydb::select($stmt,':invoiceid', $invoiceId)->items;
		foreach ($rs->trans as $item) $rs->total += $item->replyprice;
	}
	if ($debug) debugMsg($rs,'$rs');
	return $rs;
}
?>