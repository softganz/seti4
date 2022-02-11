<?php
function r_garage_order_tr_save($orderId, $data, $options = '{}') {
	$defaults = '{debug:false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	$orderInfo = R::Model('garage.order.get', $orderId);

	if (empty($data->trid)) $data->trid = NULL;
	$data->docid = $orderInfo->ordid;
	$data->shopid = $orderInfo->shopid;
	$data->qty = abs(sg_strip_money($data->qty));
	$data->price = abs(sg_strip_money($data->price));
	$data->total = $data->qty*$data->price;
	$data->created = date('U');
	$data->sorder = mydb::select('SELECT MAX(`sorder`) `nextorder` FROM %garage_ordtran% WHERE `ordid` = :docid LIMIT 1',':docid',$orderInfo->ordid)->nextorder+1;

	if ($data->stkid) {
		$stmt = 'INSERT INTO %garage_ordtran%
			(
			  `ordtrid`, `shopid`, `ordid`, `sorder`, `stkid`
			  , `qty`, `price`, `total`
			  , `created`
			)
			VALUES
			(
			  :trid, :shopid, :docid, :sorder, :stkid
			  , :qty, :price, :total
			  , :created
			)
			ON DUPLICATE KEY UPDATE
				`stkid` = :stkid
				, `qty` = :qty
				, `price` = :price
				, `total` = :total
			';
		mydb::query($stmt,$data);
		if ($debug) debugMsg(mydb()->_query);
	}

	if (empty($data->trid)) $data->trid = mydb()->insert_id;

	R::Model('garage.order.calculate', $orderId);

	if ($debug) debugMsg($data,'$orderdata');

	return $data->trid;
}
?>