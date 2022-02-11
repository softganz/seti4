<?php
/**
* iBuy Get Product Information
*
* @param Object $data
* @return Object $options
*/

$debug = true;

function r_ibuy_product_get($conditions, $options = '{}') {
	$defaults = '{debug: false, order: "t.`tpid` DESC", limit: 1, start: -1}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$result = R::Model('paper.get', $conditions, $options);

	$stmt = 'SELECT
				p.*
			, o.`name` `shopName`
			, b.`flddata` `full_description`
			FROM %ibuy_product% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %bigdata% b ON b.`keyname` = "ibuy" AND b.`keyid` = :tpid AND b.`fldname` = "description"
			WHERE p.`tpid` = :tpid
			LIMIT 1';

	$result->info = object_merge_recursive($result->info, mydb::clearprop(mydb::select($stmt, ':tpid', $result->tpid)));

	if ($debug) debugMsg($result, '$result');
	return $result;
}
?>