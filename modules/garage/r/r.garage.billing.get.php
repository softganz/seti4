<?php
function r_garage_billing_get($shopid,$billid,$options='{}') {
	$defaults='{debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;
	
	$rs=NULL;
	
	$stmt = 'SELECT
		b.*
		, i.`insurername`
		FROM %garage_billing% b
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
		WHERE (b.`shopid` = :shopid OR s.`shopparent` = :shopid) AND b.`billid` = :billid
		LIMIT 1';
	$rs = mydb::select($stmt,':shopid',$shopid, ':billid',$billid);

	if ($rs->_num_rows) {
		if (!$debug) mydb::clearprop($rs);
		$rs->billTotal=0;

		$stmt = 'SELECT
			  q.*
			, j.*
			, q.`insuclaimcode`
			FROM %garage_qt% q
				LEFT JOIN %garage_job% j USING(`tpid`)
			WHERE q.`billid`=:billid
			ORDER BY q.`tpid` ASC;
			-- {key:"qtid"}';

		$rs->qt = mydb::select($stmt,':billid',$billid)->items;

		foreach ($rs->qt as $item) $rs->billTotal += $item->replyprice;
	}
	if ($debug) debugMsg($rs,'$rs');
	return $rs;
}
?>