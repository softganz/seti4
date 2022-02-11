<?php
/**
* Garage Save AP Stock Recieve Transaction
* Created 2019-11-01
* Modify  2019-11-27
*
* @param Object $rcvInfo
* @param Object $data
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_garage_aprcv_tr_save($rcvId, $data, $options = '{}') {
	$defaults = '{value:"repairname",debug:false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$rcvInfo = R::Model('garage.aprcv.get', $rcvId);

	if (empty($data->stktrid)) $data->trid = NULL;
	$data->shopid = $rcvInfo->shopid;
	$data->stklocid = $rcvInfo->stklocid;
	$data->stkdate = $rcvInfo->rcvdate;
	$data->refcode = $rcvInfo->rcvno;
	$data->qty = abs(sg_strip_money($data->qty));
	$data->price = abs(sg_strip_money($data->price));
	$data->discountrate = abs(sg_strip_money($data->discountrate));
	$data->discountamt = abs(sg_strip_money($data->discountamt));
	$data->vatrate = abs(sg_strip_money($rcvInfo->vatrate));
	$data->vatamt = round(($data->qty * $data->price - $data->discountamt) * $data->vatrate / 100,2);
	$data->total = ($data->qty * $data->price) - $data->discountamt + $data->vatamt;
	$data->created = date('U');
	$data->sorder = mydb::select('SELECT MAX(`sorder`) `nextorder` FROM %garage_stocktran% WHERE `refcode`=:refcode LIMIT 1',$data)->nextorder+1;

	$data->stkid = SG\getFirst($data->stkid);
	if ($data->stkid) {
		$repairInfo=R::Model('garage.repaircode.get',$data->stkid);
	}
	if ($data->repairname==$repairInfo->repairname) $data->description=NULL;
	else if ($data->repairname!=$repairInfo->repairname) $data->description=$data->repairname;

	// Create Stock Recieve Transaction
	$stmt = 'INSERT INTO %garage_stocktran%
		(
			  `stktrid`, `shopid`, `stklocid`, `stkdate`, `refcode`, `sorder`
			, `stkid`, `qty`, `price`
			, `discountrate`, `discountamt`
			, `vatrate`, `vatamt`
			, `total`
			, `description`
			, `created`
		)
		VALUES
		(
			  :trid, :shopid, :stklocid, :stkdate, :refcode, :sorder
			, :stkid, :qty, :price
			, :discountrate, :discountamt
			, :vatrate, :vatamt
			, :total
			, :description
			, :created
		)
		ON DUPLICATE KEY UPDATE
			  `stkid` = :stkid
			, `qty` = :qty
			, `price` = :price
			, `total` = :total
			, `description` = :description
		';

	mydb::query($stmt,$data);

	if ($debug) debugMsg(mydb()->_query);

	$stockTrId = mydb()->insert_id;

	// Save Stock Out and Job Cost
	if ($data->jobid) {
		$data->lotid = $stockTrId;
		$data->tpid = $data->jobid;
		$data->costqty = -$data->qty;
		$data->costprice = -$data->price;
		$data->costtotal = -$data->total;
		$data->sorder = mydb::select('SELECT MAX(`sorder`) `nextorder` FROM %garage_stocktran% WHERE `refcode` = :refcode LIMIT 1',$data)->nextorder+1;

		$stmt = 'INSERT INTO %garage_stocktran%
			(
				  `stktrid`, `shopid`, `stklocid`, `stkdate`, `tpid`, `refcode`, `sorder`
				, `stkid`, `lotid`, `qty`, `price`
				, `discountrate`, `discountamt`
				, `vatrate`, `vatamt`
				, `total`
				, `description`
				, `created`
			)
			VALUES
			(
				  :trid, :shopid, :stklocid, :stkdate, :tpid, :refcode, :sorder
				, :stkid, :lotid, :costqty, :costprice
				, :discountrate, :discountamt
				, :vatrate, :vatamt
				, :costtotal
				, :description
				, :created
			)
			ON DUPLICATE KEY UPDATE
				  `stkid` = :stkid
				, `qty` = :costqty
				, `price` = :costprice
				, `total` = :costtotal
				, `description` = :description
			';

		mydb::query($stmt,$data);

		if ($debug) debugMsg(mydb()->_query);
	}

	if (empty($data->trid)) $data->trid=mydb()->insert_id;

	R::Model('garage.apmast.calculate',$rcvInfo);

	if ($data->stkid) {
		R::Model('garage.stock.cost.calculate',$data->stkid);
	}

	if ($debug) debugMsg($data,'$data');
	if ($debug) debugMsg($repairInfo,'$repairInfo');
	return $data->trid;
}
?>