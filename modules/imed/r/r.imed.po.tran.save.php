<?php
/**
* Model Name
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_imed_po_tran_save($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$data->stktrid = SG\getFirst($data->stktrid);
	$data->psnid = SG\getFirst($data->psnid);
	$data->uid = i()->uid;
	$data->orgid = SG\getFirst($data->orgId);
	$data->stkdate = $data->stkdate ? sg_date($data->stkdate, 'Y-m-d') : date('Y-m-d');
	$data->qty = abs(SG\getFirst($data->qty, 0));
	if (in_array($data->trtype, array('OUT'))) $data->qty = -$data->qty;
	$data->status = SG\getFirst($data->status);
	$data->refname = SG\getFirst($data->refname);
	$data->description = trim(SG\getFirst($data->description));
	$data->created = date('U');

	//debugMsg($data,'$data');
	
	$stmt = 'INSERT INTO %po_stktr%
					(`stktrid`, `stkid`, `uid`, `orgid`, `psnid`, `trtype`, `stkdate`, `qty`, `refname`, `status`, `description`, `created`)
					VALUES
					(:stktrid, :stkid, :uid, :orgid, :psnid, :trtype, :stkdate, :qty, :refname, :status, :description, :created)
					ON DUPLICATE KEY UPDATE
					  `stkdate` = :stkdate
					, `psnid` = :psnid
					, `stkid` = :stkid
					, `refname` = :refname
					, `status` = :status
					, `qty` = :qty
					, `description` = :description
					';
	mydb::query($stmt, $data);
	$result->query[] = mydb()->_query;

	// Update stock card balance amount
	if ($data->orgid) {
		$stmt = 'INSERT INTO %po_stk%
						(`stkid`, `orgid`, `balanceamt`)
						SELECT :stkid, :orgid, b.`balance`
						FROM
							(SELECT SUM(`qty`) `balance` FROM %po_stktr% WHERE `stkid` = :stkid AND `orgid` = :orgid) AS b
						ON DUPLICATE KEY UPDATE `balanceamt` = b.`balance`
						';
		mydb::query($stmt, $data);
		$result->query[] = mydb()->_query;
	}

	return $result;
}
?>