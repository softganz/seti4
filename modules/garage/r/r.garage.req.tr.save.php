<?php
/**
* Garage :: Save Req Transaction
* Created 2017-09-22
* Modify  2020-07-23
*
* @param Int $reqId
* @param Object $data
* @param Object $options
* @return Object
*/

$debug = true;

function r_garage_req_tr_save($reqId, $data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$reqInfo = R::Model('garage.req.get', $reqId);

	if (empty($data->stktrid)) $data->trid = NULL;
	$data->tpid = $reqInfo->tpid;
	$data->shopid = $reqInfo->shopid;
	$data->stklocid = $reqInfo->stklocid;
	$data->stkdate = $reqInfo->reqdate;
	$data->refcode = $reqInfo->reqno;
	$data->qty = -abs(sg_strip_money($data->qty));
	$data->created = date('U');
	$data->sorder = mydb::select('SELECT MAX(`sorder`) `nextorder` FROM %garage_stocktran% WHERE `refcode` = :refcode LIMIT 1',$data)->nextorder+1;

	$repairInfo = R::Model('garage.repaircode.get',$data->stkid);
	if ($data->repairname == '') $data->description = NULL;
	else if ($data->repairname != $repairInfo->repairname) $data->description = $data->repairname;

	if ($data->tpid) {
		$stmt = 'INSERT INTO %garage_stocktran%
			(
				  `stktrid`, `shopid`, `stklocid`, `stkdate`, `tpid`, `refcode`, `sorder`
				, `stkid`, `qty`
				, `created`
			)
			VALUES
			(
				  :trid, :shopid, :stklocid, :stkdate, :tpid, :refcode, :sorder
				, :stkid, :qty
				, :created
			)
			ON DUPLICATE KEY UPDATE
				  `stkid`=:stkid
				, `qty`=:qty
			';

		mydb::query($stmt,$data);

		if ($debug) debugMsg(mydb()->_query);
	}

	if (empty($data->trid)) $data->trid = mydb()->insert_id;

	//R::Model('garage.apmast.calculate',$reqInfo);

	R::Model('garage.stock.cost.calculate',$data->stkid);

	if ($debug) debugMsg($data,'$data');
	if ($debug) debugMsg($repairInfo,'$repairInfo');
	return $data->trid;
}
?>