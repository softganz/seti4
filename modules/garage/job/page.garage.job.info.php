<?php
/**
* Garage Control : Job Information Control
* Created 2020-10-08
* Modify  2020-10-08
*
* @param Object $self
* @param Object $jobInfo
* @param String $action
* @param Int $tranId
* @return String
*
* @usage garage/job/{$jobId}/info/action[/{$tranId}]
*/

$debug = true;

function garage_job_info($self, $jobInfo, $action, $tranId = NULL) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;
	$shopId = $jobInfo->shopid;

	if (!in_array($jobInfo->shopid, $shopInfo->branch)) return message('error', 'SHOP ERROR');

	$isAdmin = is_admin('garage');

	$ret = '';

	switch ($action) {
	
		case 'detail.save':
			$data = (Object) post('job');

			$data->datetoreturn = sg_date($data->datetoreturn,'Y-m-d');
			$data->milenum = sg_strip_money($data->milenum);
			$data->rcvdate = sg_date($data->rcvdate,'Y-m-d');

			$stmt = 'UPDATE %garage_job% SET
				  `rcvby` = :rcvby
				, `rcvdate` = :rcvdate
				, `cartype` = :cartype
				, `brandid` = :brandid
				, `modelname` = :modelname
				, `colorname` = :colorname
				, `plate` = :plate
				, `enginno` = :enginno
				, `bodyno` = :bodyno
				, `milenum` = :milenum
				, `insurerid` = :insurerid
				, `customerid` = :customerid
				, `insuclaimcode` = :insuclaimcode
				, `insuno` = :insuno
				, `carwaitno` = :carwaitno
				, `carinno` = :carinno
				, `commandremark` = :commandremark
				WHERE `tpid` = :jobId LIMIT 1';

			mydb::query($stmt,':jobId',$jobId, $data);
			//$ret .= mydb()->_query;
			$ret .= 'บันทึกเรียบร้อย';
			break;

		case 'data.save':
			$ret .= 'SAVE DATA '.$jobId.' COMPLETE';
			if (empty(post('value'))) {
				$result = R::Model('bigdata.json', 'remove', 'GARAGE', $jobId, 'DATA', post('group'), post('key'));
			} else {
				$result = R::Model('bigdata.json', 'save', 'GARAGE', $jobId, 'DATA', post('group'), post('key'), post('value'));
			}
			//$ret .= print_o($result,'$result');
			break;

		case 'tran.save':
			$data = (Object) post();
			$data->jobtrid = $tranId;
			$tranId = R::Model('garage.job.tr.save', $shopInfo, $jobInfo, $data,'{debug: false}');
			//$jobInfo = R::Model('garage.job.get',$shopInfo->shopid,$jobId);
			//$ret .= __garage_job_view_tran($shopInfo,$jobInfo,'hightlight',$tranId);
			break;

		case 'tran.delete':
			if ($jobId && $tranId && SG\confirm()) R::Model('garage.job.tr.delete',$jobId,$tranId,'{debug:false}');
			break;

		case 'tran.moveup':
			$thisRs = array_key_exists($tranId, $jobInfo->command) ? $jobInfo->command[$tranId] : $jobInfo->part[$tranId];

			$toRs = mydb::select('SELECT `jobtrid`,`sorder` FROM %garage_jobtr% tr LEFT JOIN %garage_repaircode% r USING(`repairid`) WHERE `tpid` = :tpid AND r.`repairtype` = :repairtype AND `sorder` < :thisorder ORDER BY `sorder` DESC LIMIT 1',':tpid',$jobId, ':repairtype',$thisRs->repairtype, ':thisorder',$thisRs->sorder);
			//$ret .= mydb()->_query.'<br />';
			//$ret .= 'This order ='.$thisRs->sorder.' TO '.$toRs->sorder.'<br />';

			if ($thisRs->jobtrid && $toRs->jobtrid) {
				mydb::query('UPDATE %garage_jobtr% SET `sorder` = :toorder WHERE `jobtrid` = :thisid LIMIT 1',':thisid',$thisRs->jobtrid,':toorder',$toRs->sorder);
				//$ret .= mydb()->_query.'<br />';
				mydb::query('UPDATE %garage_jobtr% SET `sorder` = :thisorder WHERE `jobtrid` = :toid LIMIT 1',':toid',$toRs->jobtrid,':thisorder',$thisRs->sorder);
				//$ret .= mydb()->_query.'<br />';
			}
			//$jobInfo = R::Model('garage.job.get',$shopInfo->shopid,$jobId,'{debug:false}');
			//$ret .= __garage_job_view_tran($shopInfo,$jobInfo,$action,$tranId);
			break;

		case 'tran.movedown':
			$thisRs = array_key_exists($tranId, $jobInfo->command) ? $jobInfo->command[$tranId] : $jobInfo->part[$tranId];

			$toRs = mydb::select('SELECT `jobtrid`,`sorder` FROM %garage_jobtr% tr LEFT JOIN %garage_repaircode% r USING(`repairid`) WHERE `tpid`=:tpid AND r.`repairtype`=:repairtype AND `sorder`>:thisorder ORDER BY `sorder` ASC LIMIT 1',':tpid',$jobId, ':repairtype',$thisRs->repairtype, ':thisorder',$thisRs->sorder);
			//$ret.=mydb()->_query.'<br />';
			//$ret.='This order ='.$thisRs->sorder.' TO '.$toRs->sorder.'<br />';

			if ($thisRs->jobtrid && $toRs->jobtrid) {
				mydb::query('UPDATE %garage_jobtr% SET `sorder`=:toorder WHERE `jobtrid`=:thisid LIMIT 1',':thisid',$thisRs->jobtrid,':toorder',$toRs->sorder);
				//$ret.=mydb()->_query.'<br />';
				mydb::query('UPDATE %garage_jobtr% SET `sorder`=:thisorder WHERE `jobtrid`=:toid LIMIT 1',':toid',$toRs->jobtrid,':thisorder',$thisRs->sorder);
				//$ret.=mydb()->_query.'<br />';
			}
			//$jobInfo=R::Model('garage.job.get',$shopInfo->shopid,$jobId,'{debug:false}');
			//$ret.=__garage_job_view_tran($shopInfo,$jobInfo,$action,$tranId);
			break;

		case 'part.wait':
			$stmt = 'UPDATE %garage_jobtr% SET
				`wait` = IF(`wait` > 0, NULL, 1)
				, `partrcvdate` = IF(`wait` > 0 , `partrcvdate`, :rcvdate)
				, `partrcvby` = :uid
				WHERE `jobtrid` = :jobtrid LIMIT 1';
			mydb::query($stmt, ':jobtrid', $tranId, ':rcvdate', date('Y-m-d'), ':uid', i()->uid);
			//$ret .= mydb()->_query;
			break;

		case 'process.save':
			$stmt = 'UPDATE %garage_job% SET `jobprocess` = :jobprocess WHERE `tpid` = :jobId LIMIT 1';
			mydb::query($stmt, ':jobId', $jobId, ':jobprocess', post('status'));
			//$ret .= mydb()->_query;
			break;

		case 'car.indate':
			$carindate=sg_date(post('date'),'Y-m-d');
			if (post('carnotin')) $carindate=NULL;
			$stmt = 'UPDATE %garage_job% SET `carindate` = :date WHERE `tpid` = :jobId LIMIT 1';
			mydb::query($stmt,':jobId',$jobId,':date',$carindate);
			break;

		case 'car.notin':
			$stmt = 'UPDATE %garage_job% SET `carindate` = NULL WHERE `tpid` = :jobId LIMIT 1';
			mydb::query($stmt, ':jobId', $jobId);
			break;

		case 'car.appointment':
			if (post('date')) {
				$stmt = 'UPDATE %garage_job% SET `datetoreturn` = :date, `timetoreturn` = :time WHERE `tpid` = :jobId LIMIT 1';
				mydb::query($stmt, ':jobId', $jobId, ':date', sg_date(post('date'),'Y-m-d'), ':time', post('time'));
			}
			break;

		case 'car.appointment.cancel':
			if (SG\confirm()) {
				$stmt = 'UPDATE %garage_job% SET `datetoreturn` = NULL, `timetoreturn` = NULL WHERE `tpid` = :jobId LIMIT 1';
				mydb::query($stmt, ':jobId', $jobId);

			}
			break;

		case 'car.return':
			$date = post('date')?sg_date(post('date'),'Y-m-d'):NULL;
			$iscarreturned = $date?'Yes':'No';
			$jobstatus = $date ? 6 : 3;
			$stmt = 'UPDATE %garage_job%
				SET `iscarreturned` = :iscarreturned, `returndate` = :date, `jobstatus` = :jobstatus
				WHERE `tpid` = :jobId LIMIT 1';
			mydb::query($stmt,':jobId',$jobId,':date',$date,':iscarreturned',$iscarreturned,':jobstatus',$jobstatus);
			break;

		case 'assign.save':
			$post = (Object) post();
			$post->tpid = $jobId;
			$post->uid = $tranId;
			$post->dotype = post('ty');
			$post->status = 'OPEN';
			$post->created = date('U');
			$stmt = 'INSERT INTO %garage_do%
				(`tpid`, `uid`, `dotype`, `status`, `created`)
				VALUES
				(:tpid, :uid, :dotype, :status, :created)
				ON DUPLICATE KEY UPDATE
				`dotype` = :dotype
				, `status` = "OPEN"';
			mydb::query($stmt, $post);
			//$ret .= mydb()->_query;
			break;

		case 'assign.remove':
			$stmt = 'DELETE FROM %garage_do% WHERE `tpid` = :tpid AND `uid` = :uid LIMIT 1';
			mydb::query($stmt, ':tpid', $jobId, ':uid', $tranId);
			//$ret .= mydb()->_query;
			break;

		case 'assign.leave':
			$stmt = 'UPDATE %garage_do% SET `status` = "COMPLETE" WHERE `tpid` = :jobId AND `uid` = :uid LIMIT 1';
			mydb::query($stmt, ':jobId', $jobId, ':uid', $tranId);
			//$ret .= mydb()->_query;
			break;

		case 'do.hide':
			$stmt = 'UPDATE %garage_jobtr% SET `done` = IF(`done` = -1 , NULL, -1) WHERE `jobtrid` = :jobtrid LIMIT 1';
			mydb::query($stmt, ':jobtrid', $tranId);
			//$ret .= mydb()->_query;
			break;

		case 'qt.rcvmoney':
			$data->qtid = post('qtid');
			$data->rcvmdate = post('date')?sg_date(post('date'),'Y-m-d'):NULL;
			$data->rcvmoney = sg_strip_money(post('amount'));
			$stmt = 'UPDATE %garage_qt% SET `rcvmoney` = :rcvmoney, `rcvmdate` = :rcvmdate WHERE `qtid` = :qtid LIMIT 1';
			mydb::query($stmt, $data);
			//$ret .= mydb()->_query.'<br />';
			//$ret .= print_o($data,'$data');

			$data->jobId = $jobId;
			$data->rcvmoneydate = post('date') ? sg_date(post('date'),'Y-m-d') : NULL;
			$data->rcvmoneyamt = sg_strip_money(post('amount'));
			$data->isrecieved = post('rcvall') ? 'Yes' : NULL;
			$data->newstatus = post('closejob') == 'Yes' ? 'Yes' : 'No';
			$stmt = 'UPDATE %garage_job% SET
				`isrecieved` = :isrecieved
				, `isjobclosed` = :newstatus
				, `rcvmoneydate` = (SELECT MAX(`rcvmdate`) FROM %garage_qt% WHERE `tpid` = :jobId) 
				, `rcvmoneyamt` = (SELECT SUM(`rcvmoney`) FROM %garage_qt% WHERE `tpid` = :jobId)
				WHERE `tpid` = :jobId LIMIT 1';
			mydb::query($stmt,$data);
			//$ret .= mydb()->_query.'<br />';
			break;

		case 'rcvmoney.cancel':
			if ($tranId) {
				$stmt = 'UPDATE %garage_qt% SET `rcvmoney` = NULL, rcvmdate = NULL WHERE `qtid` = :qtid LIMIT 1';
				mydb::query($stmt, ':qtid', $tranId);
				$stmt = 'UPDATE %garage_job% SET
					`rcvmoneydate` = (SELECT MAX(`rcvmdate`) FROM %garage_qt% WHERE `tpid` = :jobId) 
					, `rcvmoneyamt` = (SELECT SUM(`rcvmoney`) FROM %garage_qt% WHERE `tpid` = :jobId)
					WHERE `tpid` = :jobId LIMIT 1';
				mydb::query($stmt, ':jobId', $jobId);
			}
			break;

		case 'rcvproved.save':
			$data->jobId = $jobId;
			$data->isrcvproved = post('isrcvproved') ? 'Yes' : NULL;
			$data->newstatus = post('closejob') == 'Yes' ? 'Yes' : 'No';
			$stmt = 'UPDATE %garage_job%
				SET `isrcvproved` = :isrcvproved, `isjobclosed` = :newstatus
				WHERE `tpid` = :jobId LIMIT 1';
			mydb::query($stmt, $data);
			//$ret .= mydb()->_query;
			//$ret .= print_o(post(), 'post()');
			break;

		case 'photo.upload':
			$post = (Object) post();
			$data->tpid = $jobId;
			$data->prename = 'garage_job_'.$jobId.($post->tagname ? '_'.str_replace(',', '_', $post->tagname) : '').'_';
			$data->tagname = 'garage'.($post->tagname ? ','.$post->tagname : '');
			$data->title = $post->title;
			$data->orgid = $jobInfo->shopid;
			$data->refid = $tranId;
			$data->cid = SG\getFirst($post->cid);
			$data->deleteurl = $post->delete == 'none' ? NULL : 'garage/job/'.$jobId.'/info/photo.delete/';
			$data->link = $post->link;
			$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data,'{fileNameLength: 40}');

			if($uploadResult->error) {
				$ret = implode(' ', $uploadResult->error);
			} else {
				$ret = $uploadResult->link;
			}

			//$ret .= print_o($data,'$data');
			//$ret .= print_o($uploadResult,'$uploadResult');
			break;

		case 'photo.delete':
			if ($tranId && SG\confirm()) {
				$result = R::Model('photo.delete',$tranId);
				$ret .= 'Photo Deleted!!!';
			}
			break;

		case 'in.tran.save':
			$data = (Object) post();
			$data->trid = SG\getFirst($tranId, $data->tr);
			$trid = R::Model('garage.job.tr.save', $shopInfo, $jobInfo, $data, '{debug: false}');
			$ret = array();
			$ret['tr'] = $trid;
			$ret['post'] = post();
			$ret['data'] = $data;
			$ret['debug'] .= mydb()->_query;
			$ret['debug'] .= print_o($data, '$data');
			break;

		case 'qt.save':
			$post = (Object) post('qt');
			if ($tranId) $data->qtid = $tranId;
			$data->tpid = $jobId;
			$data->qtdate = sg_date($post->qtdate, 'Y-m-d');
			$data->insurerid = $post->insuid;
			$data->insuno = $post->insuno;
			$data->insuclaimcode = $post->claimcode;
			$qt = R::Model('garage.qt.create', $shopId, $data, '{debug: false}');
			R::Model('garage.job.status.update',$shopId, $jobId, 2);
			if ($qt->qtid) {
				location('garage/job/'.$jobId.'/qt/'.$qt->qtid);
			} else {
				$ret .= 'ERROR : Create Quation';
			}
			//$ret .= print_o($data,'$data');
			break;

		case 'qt.delete':
			if ($jobId && $tranId && SG\confirm()) {
				$isQtTran = mydb::select('SELECT `qtid` FROM %garage_jobtr% WHERE `qtid` = :qtid LIMIT 1', ':qtid', $tranId)->qtid;
				//$ret .= mydb()->_query;
				if (post('forcedelete') && $isAdmin) {
					$stmt = 'DELETE FROM %garage_qt% WHERE `tpid` = :tpid AND `qtid` = :qtid LIMIT 1';
					mydb::query($stmt, ':tpid',$jobId, ':qtid',$tranId);
					$ret .= mydb()->_query.'<br />';
					mydb::query('UPDATE %garage_jobtr% SET `qtid` = NULL WHERE `qtid` = :qtid', ':qtid', $tranId);
					$ret .= mydb()->_query.'<br />';
				} else if (empty($isQtTran)) {
					$stmt = 'DELETE FROM %garage_qt% WHERE `tpid` = :tpid AND `qtid` = :qtid LIMIT 1';
					mydb::query($stmt, ':tpid',$jobId, ':qtid',$tranId);
					//$ret .= mydb()->_query;
				} else {
					$ret .= 'ERROR : Quation not empty';
				}
				//$ret .= print_o($jobInfo->qt,'$jobInfo->qt');
			}
			break;

		case 'qt.reply':
			$reply = post('reply');
			if ($reply) {
				$reply['replywage'] = sg_strip_money($reply['replywage']);
				$reply['replypart'] = sg_strip_money($reply['replypart']);
				$reply['replyaccept'] = sg_strip_money($reply['replyaccept']);
				$reply['replyprice'] = $reply['replywage']+$reply['replypart']+$reply['replyaccept'];
				$stmt = 'UPDATE %garage_qt% SET
					`replyprice` = :replyprice
					, `replywage` = :replywage
					, `replypart` = :replypart
					, `replyaccept` = :replyaccept
					WHERE `qtid` = :qtid
					LIMIT 1';
				mydb::query($stmt,':qtid', $tranId, $reply);
				//$ret .= mydb()->_query;

				R::Model('garage.job.status.update',$shopInfo->shopid,$jobId,3);
			}
			break;

		case 'qt.tr.save':
			if ($jobId && $tranId) {
				$cmd = post('cmd');
				$part = post('part');
				if ($cmd) {
					$stmt = 'UPDATE %garage_jobtr% SET `qtid` = :qtid WHERE `tpid` = :tpid AND `jobtrid` IN (:cmd)';
					mydb::query($stmt,':tpid',$jobId, ':qtid',$tranId, ':cmd','SET:'.implode(',',$cmd));
					//$ret.=mydb()->_query;
				}
				if ($part) {
					$stmt = 'UPDATE %garage_jobtr% SET `qtid` = :qtid WHERE `tpid` = :tpid AND `jobtrid` IN (:part)';
					mydb::query($stmt,':tpid',$jobId, ':qtid',$tranId, ':part','SET:'.implode(',',$part));
					//$ret.=mydb()->_query;
				}
			}
			break;


		default:
			$ret = 'ERROR!!! No Action';
			break;

	}

	//$ret .= print_o($jobInfo,'$jobInfo');
	return $ret;
}
?>