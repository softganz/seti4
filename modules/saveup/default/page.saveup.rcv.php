<?php
/**
* Saveup Recieve Money Controller
*
* @param Object $self
* @param String $rcvId
* @param String $action
* @param Int $tranId
* @return String
*/

$debug = true;

function saveup_rcv($self, $rcvId = NULL, $action = NULL, $tranId = NULL) {
	if (empty($action) && empty($rcvId)) return R::Page('saveup.rcv.home', $self);
	else if (empty($action) && $rcvId) return R::Page('saveup.rcv.view',$self,$rcvId);

	$rcvInfo = R::Model('saveup.rcv.get', $rcvId);
	$rcvId = $rcvInfo->rcvid;


	if (empty($rcvId)) return message('error', 'No Data');

	$isEdit = user_access('administer saveups');

	switch ($action) {

		case 'recalculate' :
			$result = R::Model('saveup.rcv.update.total', $rcvId);
			$ret .= print_o($result,'$result');
			break;

		case 'delete' :
			if ($isEdit && SG\confirm() ) {

				// Calculate loan balance
				/*
				if (substr($rs->refno,0,3)=='LON') {
					$loanrs = R::Model('saveup.loan.get',$rs->refno);
					$loanpaid=0;
					foreach ($rs->tr->items as $tr) {
						if ($tr->glcode==$loanrs->glcode) $loanpaid+=$tr->amt;
					}
					if ($loanpaid>0) {
						mydb::query('UPDATE %saveup_loan% SET `balance`=`balance`+:loanpaid WHERE `loanno`=:loanno LIMIT 1',':loanno',$loanrs->loanno,':loanpaid',$loanpaid);
						$ret.=mydb()->_query;
					}
				}
				*/

				mydb::query('UPDATE %saveup_rcvmast% SET `status` = "Cancel", `memo` = :memo WHERE rcvid = :rcvid LIMIT 1', ':rcvid', $rcvId, ':memo', post('memo'));
				//$ret .= mydb()->_query;

				$stmt = 'UPDATE %saveup_log% SET `process` = -1 WHERE `keyword` = "TRANSFER" AND `process` = :rcvid';
				mydb::query($stmt, ':rcvid', $rcvId);
				//$ret .= mydb()->_query;

				//mydb::query('UPDATE %saveup_rcvtr% SET `status`="Cancel" WHERE rcvid = :rcvid',':rcvid',$rcvId);
				//mydb::query('DELETE FROM %saveup_memcard% WHERE refno = :rcvno',':rcvno',$rcvInfo->rcvno);
				saveup_model::log('keyword','CANCEL','status',20,'detail','ใบรับเงินเลขที่ '.$rcvid.' ถูกยกเลิกโดย '.i()->username.'('.i()->uid.')');
				location('saveup/rcv');
			} else {
				location('saveup/rcv/'.$rcvId);
			}
			break;

		case 'tr.remove' :
			if ($tranId && array_key_exists($tranId, $rcvInfo->trans) && SG\confirm()) {
				// Remove from Member Card
				$stmt = 'DELETE FROM %saveup_memcard% WHERE `refno` = :refno AND `trno` = :trno LIMIT 1';
				mydb::query($stmt, ':refno', $rcvNo, ':trno', $tranId);
				//$ret .= mydb()->_query.'<br />';

				// Remove from Recieve Transaction
				$stmt = 'DELETE FROM %saveup_rcvtr% WHERE `aid` = :aid  LIMIT 1';
				mydb::query($stmt, ':aid', $tranId);
				//$ret .= mydb()->_query.'<br />';

				// Re-calculate Loan
				if (substr($rcvInfo->trans[$tranId]->refno,0,3) == 'LON') {
					R::Model('saveup.loan.update.balance', $rcvInfo->trans[$tranId]->refno);
				}

				R::Model('saveup.rcv.update.total', $rcvId);

			}
			//$ret .= 'Remove Tran Id '.$tranId.'<br />'.print_o($rcvInfo, '$rcvInfo');
			break;

		case 'tr.add' :
			$ret .= '<h3>ADD Tran</h3>';
			$postRcv = (object) post('rcv');

			if ($postRcv->period) {
				$postRcv->rcvdate = sg_date($postRcv->rcvdate, 'Y-m-d');
				$postRcv->transamt = sg_strip_money($postRcv->transamt);
				$stmt = 'UPDATE %saveup_rcvmast% SET `period` = :period, `rcvdate` = :rcvdate, `transamt` = :transamt, `transby` = :transby WHERE `rcvid` = :rcvid LIMIT 1';
				mydb::query($stmt, ':rcvid', $rcvId, $postRcv);
				//$ret .= mydb()->_query.'<br />';

				// Change period
				if ($postRcv->period != $rcvInfo->period) {
					$stmt = 'UPDATE %saveup_rcvtr% SET `period` = :newperiod WHERE `rcvid` = :rcvid AND `period` = :oldperiod';
					mydb::query($stmt, ':rcvid', $rcvId, ':oldperiod', $rcvInfo->period, ':newperiod', $postRcv->period);
					//$ret .= mydb()->_query.'<br />';
				}

				// Update date on memcard
				if ($postRcv->rcvdate != $rcvInfo->rcvdate) {
					$stmt = 'UPDATE %saveup_memcard% c
										LEFT JOIN %saveup_rcvtr% tr ON tr.`aid` = c.`trno` AND LEFT(c.`refno`,3) = "RCV"
									SET c.`date` = :newdate
									WHERE tr.`rcvid` = :rcvid';
					mydb::query($stmt, ':rcvid', $rcvId, ':newdate', $postRcv->rcvdate);
					//$ret .= mydb()->_query.'<br />';
				}
			}

			$postRcvtr = post('rcvtr');
			foreach ($postRcvtr['mid'] as $key => $mid) {
				if ($mid && $postRcvtr['name'][$key] && $postRcvtr['glcode'][$key] && $postRcvtr['amt'][$key]) {
					$amt = sg_strip_money($postRcvtr['amt'][$key]);
					$rcvtrList[] = array(
										'period'=>$postRcvtr['period'][$key],
										'mid'=>$mid,
										'name'=>$postRcvtr['name'][$key],
										'glcode'=>$postRcvtr['glcode'][$key],
										'loanno'=>$postRcvtr['loanno'][$key],
										'amt'=>$amt,
										);
					$total += $amt;
				}
			}

			if ($rcvtrList) {
				$result = R::Model('saveup.rcv.tran.add', $rcvInfo, $rcvtrList);
			}

			$ret .= 'Add transaction completed.';
			//$ret .= print_o($postRcv, '$postRcv');
			//$ret .= print_o($rcvtrList, '$rcvtrList');
			//$ret .= print_o($result, '$result');
			//$ret .= print_o(post(),'post()');
			break;

		default :
			if (empty($rcvInfo)) $rcvInfo = $rcvId;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'saveup.rcv.'.$action,
								$self,
								$rcvInfo,
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

	//$ret .= print_o($rcvInfo,'rcvInfo');

	return $ret;
}
?>