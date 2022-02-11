<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	if (empty($action) && empty($orgId)) {
		return R::Page('imed.pocenter.home',$self);
	} else if (empty($action) && $orgId) {
		return R::Page('imed.pocenter.view',$self,$orgId);
	}

	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	$isAdmin = user_access('administer imeds') || $orgInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $orgInfo->is->officer
		|| in_array($orgInfo->officers[i()->uid], ['ADMIN','MODERATOR']);

	// DO submodule controller
	//R::View('imed.toolbar', $self, 'ศูนย์กายอุปกรณ์', 'pocenter', $orgInfo);

	$ret = '';

	switch ($action) {

		case 'stock.tr.save':
			$data = (object) post('stk');
			if ($isEditable) {
				$data->stktrid = SG\getFirst($data->stktrid);
				$data->psnid = SG\getFirst($data->psnid);
				$data->orgid = $orgId;
				$data->stkdate = sg_date($data->stkdate, 'Y-m-d');
				$data->qty = abs(SG\getFirst($data->qty, 0));
				if (in_array($data->trtype, array('OUT'))) $data->qty = -$data->qty;
				$data->description = trim(SG\getFirst($data->description));
				$data->created = date('U');
				//$ret .= print_o($data,'$data');
				
				$stmt = 'INSERT INTO %po_stktr%
					(`stktrid`, `stkid`, `orgid`, `psnid`, `trtype`, `stkdate`, `qty`, `refname`,`description`, `created`)
					VALUES
					(:stktrid, :stkid, :orgid, :psnid, :trtype, :stkdate, :qty, :refname, :description, :created)
					ON DUPLICATE KEY UPDATE
					  `stkdate` = :stkdate
					, `psnid` = :psnid
					, `refname` = :refname
					, `qty` = :qty
					, `description` = :description
					';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;

				// Update stock card balance amount
				$stmt = 'INSERT INTO %po_stk%
					(`stkid`, `orgid`, `balanceamt`)
					SELECT :stkid, :orgid, b.`balance`
					FROM
						(SELECT SUM(`qty`) `balance` FROM %po_stktr% WHERE `stkid` = :stkid AND `orgid` = :orgid) AS b
					ON DUPLICATE KEY UPDATE `balanceamt` = b.`balance`
					';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
			}
			break;


		case 'stock.tr.edit':
			$stmt = 'SELECT * FROM %po_stktr% WHERE `stktrid` = :stktrid AND `orgid` = :orgid LIMIT 1';
			$data = mydb::select($stmt, ':stktrid', $tranId, ':orgid', $orgId);
			if ($data->trtype == 'IN') {
				$ret .= R::Page('imed.pocenter.stock.in', $self, $orgId, $data);
			} else if ($data->trtype == 'OUT') {
				$ret .= R::Page('imed.pocenter.stock.out', $self, $orgId, $data);
			}
			//$ret .= print_o($data,'$data');
			return $ret;


		case 'stock.tr.remove':
			if ($isEditable && $tranId && SG\confirm()) {
				$stkTran = mydb::select('SELECT * FROM %po_stktr% WHERE `stktrid` = :stktrid AND `orgid` = :orgid LIMIT 1', ':stktrid', $tranId, ':orgid', $orgId);

				$stmt = 'DELETE FROM %po_stktr% WHERE `stktrid` = :stktrid AND `orgid` = :orgid LIMIT 1';
				mydb::query($stmt, ':stktrid', $tranId, ':orgid', $orgId);
				//$ret .= mydb()->_query;

				// Update stock card balance amount
				$stmt = 'INSERT INTO %po_stk%
					(`stkid`, `orgid`, `balanceamt`)
					SELECT :stkid, :orgid, b.`balance`
					FROM
						(SELECT SUM(`qty`) `balance` FROM %po_stktr% WHERE `stkid` = :stkid AND `orgid` = :orgid) AS b
					ON DUPLICATE KEY UPDATE `balanceamt` = b.`balance`
					';
				mydb::query($stmt, ':stkid', $stkTran->stkid, ':orgid', $orgId);
				//$ret .= mydb()->_query;
			}
			break;


		default:
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'imed.pocenter.'.$action,
				$self,
				$orgInfo,
				func_get_arg($argIndex),
				func_get_arg($argIndex+1),
				func_get_arg($argIndex+2),
				func_get_arg($argIndex+3),
				func_get_arg($argIndex+4)
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';
			break;
	}

	return $ret;
}
?>