<?php
/**
* iBuy Get Order Information
*
* @param Object $data
* @return Object $options
*/

$debug = true;

function r_ibuy_order_get($conditions, $options = '{}') {
	$defaults = '{debug: false, limit: 1, start: -1}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) {
	} else if (is_array($conditions)) {
		$conditions = (object)$conditions;
	} else {
		$conditions = (Object) ['orderId' => $conditions];
	}

	$orderId = $conditions->orderId;

	$stmt = 'SELECT
				o.*
			, f.`custname`
			, u.`name`
			, f.`custaddress`
			, f.`custzip`
			, f.`custphone`
			, f.`custattn`
			, f.`shippingby`
			FROM %ibuy_order% o
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %ibuy_customer% f USING(`uid`)
			WHERE `oid` = :orderId
			LIMIT 1';

	$rs = mydb::select($stmt,':orderId', $orderId);

	if ($rs->_empty) return $result;

	$result->oid = $rs->oid;
	$result->info = mydb::clearprop($rs);


	$stmt = 'SELECT o.*,t.`title`
		FROM %ibuy_ordertr% o
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE o.`oid` = :orderId
		ORDER BY t.`title` ASC;
		-- {key: "otrid"}';
	$orderTran = mydb::select($stmt,':orderId',$orderId);

	$result->trans = $orderTran->items;

	$stmt = 'SELECT
		  l.*
		, u.`name`
		FROM %ibuy_log% l
			LEFT JOIN %users% u USING(`uid`)
		WHERE keyword = "order" AND kid = :orderId
		ORDER BY `lid` ASC';
	$logs = mydb::select($stmt, ':orderId', $orderId);

	$result->logs = $logs->items;

	if ($debug) debugMsg($result, '$result');

	return $result;
}
?>