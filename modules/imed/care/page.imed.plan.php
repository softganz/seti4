<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_care($self, $psnId = NULL, $action = NULL, $tranId = NULL) {
	if (!is_numeric($psnId)) {$action = $psnId; unset($psnId);} // Action as psnId and clear

	if (empty($action) && empty($psnId)) return R::Page('imed.care.home',$self);
	if (empty($action) && $psnId) {
		return R::Page('imed.care.view',$self,$psnId);
	}


	$psnInfo = R::Model('imed.patient.get', $psnId, '{data: "info"}');
	$psnId = $psnInfo->psnId;

	if (!$psnId) return message('error','ไม่มีข้อมูล');

	$isDisabled = !$psnInfo->disabled->_empty;
	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) return message('error', 'Access Denied');


	switch ($action) {
		case 'create':
			$data = new stdClass;
			$data->psnid = $psnId;
			$data->orgid = post('org');
			$data->uid = i()->uid;
			$data->datemake = date('Y-m-d');
			$data->created = date('U');
			$stmt = 'INSERT INTO %imed_careplan% (`psnid`, `orgid`, `uid`, `datemake`, `created`) VALUES (:psnid, :orgid, :uid, :datemake, :created)';
			mydb::query($stmt, $data);
			$careId = mydb()->insert_id;

			//$ret .= mydb()->_query;
			$ret .= R::Page('imed.care.plan', NULL, $psnInfo, $careId);
			break;

		case 'plan.save':
			$data = (object) post('data');
			$data->cpid = $tranId;
			$data->datemake = sg_date($data->datemake, 'Y-m-d');
			$stmt = 'UPDATE %imed_careplan% SET
							`datemake` = :datemake
							, `diagnose` = :diagnose
							, `tai` = :tai
							, `adl` = :adl
							, `conceptlive` = :conceptlive
							, `conceptlong` = :conceptlong
							, `caution` = :caution
							, `problem` = :problem
							, `targetshort` = :targetshort
							, `servicewant` = :servicewant
							WHERE `cpid` = :cpid
							LIMIT 1';
			mydb::query($stmt, $data);
			$ret .= mydb()->_query;

			break;

		case 'delete':
			// TODO : Delete all transaction of care plan too
			// TODO : Delete service seq relate to plan tran
			if ($psnId && $tranId && $isEdit && SG\confirm()) {
				$stmt = 'DELETE FROM %imed_careplan% WHERE `psnid` = :psnid AND `cpid` = :cpid LIMIT 1';
				mydb::query($stmt, ':psnid', $psnId, ':cpid', $tranId);
				//$ret .= mydb()->_query;

				$stmt = 'DELETE FROM %imed_careplantr% WHERE `cpid` = :cpid';
				mydb::query($stmt, ':cpid', $tranId);

				$ret .= $result->error ? $result->error : 'Delete Care Plan Complete';
			} else {
				$ret .= 'ERROR on delete';
			}
			break;

		case 'plan.tran.save':
			if ($isEdit) {
				$data = (object) post('data');
				$data->cptrid = SG\getFirst($data->cptrid);
				$data->cpid = $tranId;
				$data->plandate = sg_date(SG\getFirst($data->plandate, date('Y-m-d')),'Y-m-d');
				$data->uid = i()->uid;
				$data->created = date('U');

				$stmt = 'INSERT INTO %imed_careplantr%
								(`cptrid`, `cpid`, `uid`, `plandate`, `plantime`, `carecode`, `detail`, `created`)
								VALUES
								(:cptrid, :cpid, :uid, :plandate, :plantime, :carecode, :detail, :created)
								ON DUPLICATE KEY UPDATE
								`plandate` = :plandate
								, `plantime` = :plantime
								, carecode = :carecode
								, detail = :detail
								';

				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;

				//$ret .= 'SAVE TRAN '.print_o($data,'$data');
				$careInfo = R::Model('imed.care.get', $tranId);
				$orgInfo = R::Model('imed.social.get', $careInfo->orgid, '{data: "info"}');
				$ret .= R::View('imed.care.plan.tran', $psnInfo, $careInfo, $orgInfo);
			}
			break;

		case 'plan.tran.edit':
			if ($isEdit) {
				$careInfo = R::Model('imed.care.get', $tranId);
				$data = $careInfo->plan[post('tr')];
				if ($data) {
					$ret .= R::Page('imed.care.plan.tran.add', NULL, $psnId, $tranId, $data);
				} else {
					$ret .= 'ไม่มีข้อมูล';
				}
			}
			break;

		case 'plan.tran.delete':
			if ($isEdit) {
				$careInfo = R::Model('imed.care.get', $tranId);
				$data = $careInfo->plan[post('tr')];
				if ($data) {
					$stmt = 'DELETE FROM %imed_careplantr% WHERE `cptrid` = :cptrid LIMIT 1';
					mydb::query($stmt, ':cptrid', post('tr'));
					//$ret .= mydb()->_query;
				}
				//$ret .= print_o($data,'$data');
			}
			break;

		case 'plan.tran.done':
			if (!$isEdit) return $ret;
			$cptrid = post('tr');
			$careInfo = R::Model('imed.care.get', $tranId);
			$careTran = $careInfo->plan[$cptrid];
			$data = (object) post('data');
			//$ret .= 'SAVE '.$tranId.print_o($data,'$data').print_o($careTran,'$careTran');
			if (empty($careTran)) return $ret;

			if ((array) $data) {
				// Save care result
				// Create service
				$data->cptrid = $cptrid;
				$data->uid = i()->uid;
				$data->psnid = $psnId;
				$data->service = 'Care Plan';
				$data->appsrc = 'Web';
				$data->rx = $data->doneDetail;
				$data->timedata = sg_date(sg_date($data->donedate,'Y-m-d').' '.$data->donetime.':00','U');
				//$ret .= sg_date($data->donedate,'Y-m-d').' '.$data->donetime.':00'.'<br />';
				$data->created = date('U');
				$stmt = 'INSERT INTO %imed_service%
								(`seq`, `uid`, `pid`, `service`, `appsrc`, `rx`, `timedata`, `created`)
								VALUES
								(:seq, :uid, :psnid, :service, :appsrc, :rx, :timedata, :created)
								ON DUPLICATE KEY UPDATE
								  `timedata` = :timedata
								, `rx` = :doneDetail
								';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query.'<br />';

				if (empty($data->seq)) $data->seq = mydb()->insert_id;
				$stmt = 'UPDATE %imed_careplantr% SET `seq` = :seq, `status` = 1 WHERE `cptrid` = :cptrid LIMIT 1';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;

				$careInfo = R::Model('imed.care.get', $tranId);
				$orgInfo = R::Model('imed.social.get', $careInfo->orgid, '{data: "info"}');
				$ret .= R::View('imed.care.plan.tran', $psnInfo, $careInfo, $orgInfo);
			} else {
				$ret .= R::Page('imed.care.plan.tran.done', $self, $psnInfo, $tranId, $careTran);
			}
			break;
		/*
		case 'rehab.add':
			$ret .= 'Add';
			$stmt = 'INSERT INTO %imed_care% (`pid`,`careid`, `status`, `uid`, `created`) VALUES (:pid, :careid, 1, :uid, :created)';
			mydb::query($stmt, ':pid',$psnId, ':careid',_IMED_CARE_REHAB, ':uid',i()->uid, ':created', date('U'));
			break;

		case 'rehab.remove':
			if ($isEdit && SG\confirm()) {
				$stmt = 'DELETE FROM %imed_care% WHERE `pid` = :psnid AND `careid`=:careid LIMIT 1';
				mydb::query($stmt,':psnid',$psnId,':careid',_IMED_CARE_REHAB);
			}
			break;

		case 'elder.remove':
			if ($isEdit && SG\confirm()) {
				mydb::query('DELETE FROM %imed_care% WHERE `pid`=:pid AND `careid`=:careid LIMIT 1',':pid',$psnId,':careid',_IMED_CARE_ELDER);
			}
			break;

		case 'elder.add':
			mydb::query('INSERT INTO %imed_care% (`pid`,`careid`, `status`, `uid`, `created`) VALUES (:pid, :careid, 1, :uid, :created)', ':pid',$psnId, ':careid',_IMED_CARE_ELDER, ':uid',i()->uid, ':created', date('U'));
			break;
		*/


		default:
			$argIndex = 3; // Start argument

			//$ret .= 'PAGE IMED psnId = '.$psnId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex).'<br />';
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'imed.care.'.$action,
								$self,
								$psnInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);
			if (is_string($ret) && trim($ret) == '') $ret = 'ERROR : PAGE NOT FOUND';
			break;
	}

	//$ret .= 'Action = '.$action. ' Is create = '.($isCreatable ? 'YES' : 'NO').'<br />';
	//$ret .= print_o($psnInfo, '$psnInfo');

	return $ret;
}
?>