<?php
/**
* Project Action Person Join by Register, Invite or Walkin
* Created 2019-02-22
* Modify  2019-07-30
*
* @param Object $self
* @param Int $tpid
* @param Int $calId
* @param String $action
* @param Int $tranId
* @param _POST Array $reg
* @return String
*/

$debug = true;

// TODO : Edit information by register using refcode

function project_join($self, $tpid = NULL, $calId = NULL, $action = NULL, $tranId = NULL) {

	if (empty($action) && empty($tpid) && empty($calId)) return R::Page('project.join.home',$self);

	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true, data: "info"}');
	$tpid = $projectInfo->tpid;
	$projectInfo->calid = NULL;

	if ($calId) {
		$calendarInfo = R::Model('project.calendar.get', $calId);
		$doingInfo = R::Model('org.doing.get', array('calid' => $calId),'{data: "info"}');
		$projectInfo->calid = $calendarInfo->calid;
		$projectInfo->calendarInfo = $calendarInfo;
		$projectInfo->doingInfo = $doingInfo;
	}

	if (empty($action) && $tpid && empty($calId)) return R::Page('project.join.action',$self, $projectInfo);
	if (empty($action) && $tpid && $calId) return R::Page('project.join.info',$self, $projectInfo);




	$isAuthRefCode = $_SESSION['auth.join.refcode'];
	$isProjectMember = $projectInfo->info->membershipType;
	$isAcces = $projectInfo->RIGHT & _IS_ACCESS;

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (in_array($action, array('view','edit','cancel')) && $tranId) {
		$joinInfo = R::Model('project.join.get', array('psnid' => $tranId, 'calid' => $calId));
		if ($joinInfo->_empty) $joinInfo = NULL;
		$isRegisterOwner = i()->ok && $joinInfo->uid == i()->uid;
		$isViewable = $isEdit || $isProjectMember || $isRegisterOwner || $isAuthRefCode == $joinInfo->refcode;
		$isEdit = ($projectInfo->RIGHT & _IS_EDITABLE) || $isRegisterOwner || $isAuthRefCode == $joinInfo->refcode;
	}

	//debugMsg($doingInfo,'$doingInfo');
	//debugMsg($calendarInfo,'$calendarInfo');
	//debugMsg($projectInfo,'$projectInfo');

	R::View('project.toolbar', $self, $calendarInfo->title, 'join', $projectInfo);

	if ($calId && empty($doingInfo->doid))
		return message('error','ข้อผิดพลาด : ไม่มีข้อมูลกิจกรรม หรือ กิจกรรมยังไม่เปิดให้ลงทะเบียน');

	switch ($action) {
		case 'register':
			$data = new stdClass();
			$data->tpid = $tpid;
			$data->calid = $calId;
			$data->regtype = 'Register';
			$data->calendarTitle = $calendarInfo->title;

			$formOption = new stdClass;
			$formOption->mode = 'register';

			$ret .= R::View('project.join.register.form', $data, $formOption);
			break;


		case 'walkin':
			$data = new stdClass();
			$data->tpid = $tpid;
			$data->calid = $calId;
			$data->regtype = 'Walk In';

			$formOption = new stdClass;
			$formOption->mode = 'register';

			$ret .= R::View('project.join.register.form', $data, $formOption);
			break;


		// DONE : Check right before invite
		case 'invite':
			$data = new stdClass();
			$data->registerrem = $doingInfo->registerrem;
			$data->paidgroup = $doingInfo->paidgroup;

			$formOption = new stdClass;
			$formOption->mode = 'register';
			$formOption->accessPerson = false;
			if (in_array($projectInfo->doingInfo->isregister, array(1,2,8)) && !i()->ok) {
				return R::View('signform');
			}
			if ($isAcces || $isProjectMember) {
				$data->tpid = $tpid;
				$data->calid = $calId;
				$data->regtype = 'Invite';
				$data->registerBy = 'member';
				$formOption->accessPerson = true;
				$ret .= R::View('project.join.register.form', $data, $formOption);
			} else if (i()->ok && $projectInfo->doingInfo->isregister == 8) {
				$data->tpid = $tpid;
				$data->calid = $calId;
				$data->regtype = 'Invite';
				$data->registerBy = 'user';
				$ret .= R::View('project.join.register.form', $data, $formOption);
			} else if ($projectInfo->doingInfo->isregister == 9) {
				$data->tpid = $tpid;
				$data->calid = $calId;
				$data->regtype = 'Invite';
				$data->registerBy = 'public';
				$ret .= R::View('project.join.register.form', $data, $formOption);
			} else {
				$ret .= message('error', 'Access Denied');
			}
			break;


		case 'registersave':
			$data = (object) post('reg');

			//$ret .= print_o($data, '$data');

			if (empty($data->psnid) && $data->cid) {
				$isCidRegistered = R::Model('project.join.get', array('calid' => $calId, 'cid' => $data->cid));
				if ($isCidRegistered) {
					$ret .= message('error', 'เลขประจำตัวบัตรประชาชนนี้ได้ลงทะเบียนไว้เรียบร้อยแล้ว : ไม่สามารถลงทะเบียนซ้ำได้');
					$ret .= R::View('project.join.register.form', $data);
					return $ret;
				}
				//$ret .= print_o($isCidRegistered, '$isCidRegistered');
			}

			$resultPerson = R::Model('person.save', $data);

			$data->doid = $doingInfo->doid;

			$resultJoin = R::Model('project.join.save', $data);
			
			//$ret .= print_o($resultPerson, '$resultPerson');
			//$ret .= print_o($resultJoin, '$resultJoin');
			//$ret .= print_o($data, '$data');
			//return $ret;

			if (empty($data->doid)) {
				$ret .= '<p class="notify">ERROR ON SAVING...</p>';
			} else if ($data->regtype == 'Register') {
				$_SESSION['auth.join.refcode'] = $data->refcode;
				$_SESSION['auth.join.cid'] = $data->cid;
				$_SESSION['auth.join.phone'] = $data->phone;
				location('project/join/'.$tpid.'/'.$calId.'/modify');
				//location('project/join/'.$tpid.'/'.$calId.'/ref/'.$data->refcode);
			} else if ($data->regtype == 'Walk In') {
				// Update isjoin = 1
				$stmt = 'UPDATE %org_dos% SET `isjoin` = 1 WHERE `psnid` = :psnid AND `doid` = :doid LIMIT 1';
				mydb::query($stmt, ':psnid', $data->psnid, ':doid', $data->doid);
				//$ret .= mydb()->_query;
				location('project/join/'.$tpid.'/'.$calId.'/ref/'.$data->refcode);
			} else {
				location('project/join/'.$tpid.'/'.$calId.'/list', array('group'=>$data->joingroup));
			}
			break;
	

		//================================
		// View , Edit , Cancel => register can do
		//================================

		case 'view':
			if ($joinInfo && $isViewable) {
				$formOption = new stdClass;
				$formOption->mode = 'view';
				if ($isEdit) $formOption->isEdit = true;
				$ret .= R::View('project.join.register.form', $joinInfo, $formOption);
			} else {
				return message('error', 'ERROR : ไม่มีข้อมูลตามที่ระบุ');
			}
			//$ret .= print_o($joinInfo, '$joinInfo');
			break;


		case 'edit':
			if ($joinInfo && ($isEdit || $joinInfo->uid == i()->uid)) {
				$formOption = new stdClass;
				$formOption->mode = 'edit';
				if ($isProjectMember) $formOption->accessPerson = true;
				if ($isEdit) $formOption->isEdit = true;
				$ret .= R::View('project.join.register.form', $joinInfo, $formOption);
			} else {
				$ret .= message('error', 'Access Denied');
			}
			//$ret .= print_o($joinInfo, '$joinInfo');
			break;


		case 'cancel':
			if ($tranId && $joinInfo->doid
					&& ($isEdit || $joinInfo->uid == i()->uid)) {
				$stmt = 'UPDATE %org_dos% SET `isjoin` = IF(`isjoin` = -1 , 0, -1) WHERE `psnid` = :psnid AND `doid` = :doid LIMIT 1';
				mydb::query($stmt, ':psnid', $tranId, ':doid', $joinInfo->doid);
				//$ret .= mydb()->_query.'<br />';
				//$ret .= print_o($joinInfo,'$joinInfo');
				$ret .= 'ยกเลิการลงทะเบียนเรียบร้อย';
			} else {
				$ret .= message('error', 'Access Denied');
			}
			break;


		//================================
		// All after this only team can do
		//================================

		case 'delete':
			$joinInfo = R::Model('project.join.get', array('psnid' => $tranId, 'calid' => $calId));
			if (SG\confirm() && $joinInfo->doid
					&& ($isEdit || $joinInfo->uid == i()->uid)) {
				$stmt = 'DELETE FROM %org_dos% WHERE `psnid` = :psnid AND `doid` = :doid LIMIT 1';
				mydb::query($stmt, ':psnid', $tranId, ':doid', $joinInfo->doid);
				//$ret .= mydb()->_query.'<br />';

				$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "join" AND `part` = "register" AND `calid` = :calid AND `refcode` = :psnid';
				mydb::query($stmt, ':tpid', $tpid, ':psnid', $tranId, ':calid', $calId);
				//$ret .= mydb()->_query.'<br />';

				$ret .= 'ลบข้อมูลเรียบร้อย';
			} else {
				$ret .= message('error', 'Access Denied');
			}
			break;


		case 'proved':
			$joinInfo = R::Model('project.join.get', array('psnid' => $tranId, 'calid' => $calId));
			if ($isEdit) {
				$stmt = 'UPDATE %org_dos% SET `isjoin` = IF(`isjoin` = 1 , 0, 1) WHERE `psnid` = :psnid AND `doid` = :doid LIMIT 1';
				mydb::query($stmt, ':psnid', $tranId, ':doid', $joinInfo->doid);
			}
			break;


		case 'check':
			$result = array();
			$result['ok'] = false;
			$result['tpid'] = intval($tpid);
			$result['calid'] = intval($calId);
			$result['psnid'] = intval($tranId);

			$joinInfo = R::Model('project.join.get', array('psnid' => $tranId, 'calid' => $calId));

			//if ($joinInfo->)
			//print_o($joinInfo,'$joinInfo',1);
			if ($joinInfo->psnid == $tranId) {
				$result['ok'] = true;
				$result['fullname'] = $joinInfo->firstname.' '.$joinInfo->lastname;
				$result['firstname'] = $joinInfo->firstname;
				$result['lastname'] = $joinInfo->lastname;
				$result['joingroup'] = $joinInfo->joingroup;
			}
			return $result;


		case 'rcv.not':
			$joinInfo = R::Model('project.join.get', array('psnid' => $tranId, 'calid' => $calId));
			if ($isEdit) {
				$stmt = 'UPDATE %org_dos% SET `isjoin` = IF(`isjoin` = 3 , 2, 3) WHERE `psnid` = :psnid AND `doid` = :doid LIMIT 1';
				mydb::query($stmt, ':psnid', $tranId, ':doid', $joinInfo->doid);
			}
			break;


		case 'rcv.save' :
			$data = (object) post('rcv');
			$data->tpid = $tpid;
			$data->calid = $calId;
			$data->psnid = $tranId;

			if (empty($data->dopid)) {
				// Can delete :: $joinInfo = R::Model('project.join.get', array('calid' => $calId, 'psnid' => $tranId) );
				$data->doid = $doingInfo->doid;
				$data->psnid = $tranId;
				$data->paiddate = empty($data->paiddate) ? date('Y-m-d') : sg_date($data->paiddate,'Y-m-d');
				$data->agrno = $projectInfo->info->agrno;
				$data->projecttitle = $projectInfo->info->title;
			}

			$data->tr = post('tr');

			$result = R::Model('project.join.rcv.save', $data, '{debug: false}');
			//$ret .= htmlspecialchars(sg_json_encode($result)).'<br />';
			//$ret .= print_o($result, '$result');
			//$ret .= print_o($data, '$data');
			//location('project/join/'.$tpid.'/'.$calId.'/rcv/'.$result->data->dopid);
			break;


		case 'rcv.delete':
			if ($isEdit && SG\confirm()) {
				$stmt = 'DELETE FROM %org_dopaidtr% WHERE `dopid` = :dopid';
				mydb::query($stmt, ':dopid', $tranId);
				//$ret .= mydb()->_query.'<br />';

				$stmt = 'DELETE FROM %org_dopaid% WHERE `dopid` = :dopid LIMIT 1';
				mydb::query($stmt, ':dopid', $tranId);
				//$ret .= mydb()->_query.'<br />';
				$ret .= 'ลบข้อมูลเรียบร้อย';
			} else {
				$ret .= 'Access Denied';
			}
			break;


		case 'rcv.tr.edit':
			$dopid = mydb::select('SELECT `dopid` FROM %org_dopaidtr% WHERE `doptrid` = :doptrid LIMIT 1', ':doptrid', $tranId)->dopid;
			$dopaidInfo=R::Model('org.dopaid.doc.get', $dopid);

			if ($isEdit
				&& $dopaidInfo->tpid == $tpid && $dopaidInfo->calid == $calId
				&& $dopaidInfo->trans[$tranId]) {
				$ret .= R::Page('project.join.addrcvtr', NULL, $projectInfo, $dopid, $dopaidInfo->trans[$tranId]);
				//$ret .= print_o($dopaidInfo->trans[$tranId],$data);
			}
			break;


		case 'rcv.tr.delete':
			$dopid = mydb::select('SELECT `dopid` FROM %org_dopaidtr% WHERE `doptrid` = :doptrid LIMIT 1', ':doptrid', $tranId)->dopid;
			$dopaidInfo=R::Model('org.dopaid.doc.get', $dopid);

			if ($isEdit && SG\confirm()
				&& $dopaidInfo->tpid == $tpid && $dopaidInfo->calid == $calId
				&& $dopaidInfo->trans[$tranId]) {
				$stmt = 'DELETE FROM %org_dopaidtr% WHERE `doptrid` = :doptrid LIMIT 1';
				mydb::query($stmt, ':doptrid', $tranId);
				$result = R::Model('org.dopaid.update.total', $dopid, '{debug: false}');
				$ret .= 'ลบข้อมูลเรียบร้อย';
				//$ret .= print_o($result, '$result');
			} else {
				$ret .= 'ERROR : Invalid condition';
			}
			break;


		case 'rcv.locked':
			if ($isEdit) {
				$stmt = 'UPDATE %org_dopaid% SET `islock` = IF(`islock` = 1 , 0, 1) WHERE `dopid` = :dopid LIMIT 1';
				mydb::query($stmt, ':dopid', $tranId);
				$ret .= mydb()->_query;
			}
			break;


		// TODO : Scan QR Code to view or edit by input CID or Phone to Auth
		case 'print':
			$joinInfo = R::Model('project.join.get', array('refcode' => $tranId, 'calid' => $calId));
			$ret .= R::View('project.join.register.form', $joinInfo, '{mode: "view"}');
			//$ret .= print_o($joinInfo, '$joinInfo');
			break;


		case 'moveup':
			$doid = $doingInfo->doid;
			$thisRs = mydb::select('SELECT * FROM %org_dos% WHERE `doid` = :doid AND `psnid` = :psnid LIMIT 1', ':doid', $doid, ':psnid',$tranId);

			if (empty($thisRs->printweight)) {
				// Move to last printweight
				$toRs->printweight = mydb::select('SELECT MAX(`printweight`) `maxWeight` FROM %org_dos% WHERE `doid` = :doid LIMIT 1', ':doid', $doid)->maxWeight+1;

				mydb::query('UPDATE %org_dos% SET `printweight` = :toorder WHERE `doid` = :doid AND `psnid` = :psnid LIMIT 1', ':doid', $doid ,':psnid',$thisRs->psnid,':toorder',$toRs->printweight);
				//$ret.=mydb()->_query.'<br />';
				$ret .= 'Move up completed.';
			} else {
				$toRs = mydb::select('SELECT `psnid`,`printweight` FROM %org_dos% tr WHERE `doid` = :doid AND `printweight` < :printweight ORDER BY `printweight` DESC LIMIT 1',$thisRs);
				//$ret .= mydb()->_query.'<br />';
				//$ret .= 'This order = '.$thisRs->printweight.' TO '.$toRs->printweight.'<br />'.print_o($thisRs,'$thisRs').print_o($toRs,'$toRs');

				if ($toRs->_empty) $toRs->printweight = 1;

				if ($thisRs->printweight && $toRs->printweight) {
					mydb::query('UPDATE %org_dos% SET `printweight` = :toorder WHERE `doid` = :doid AND `psnid` = :thisid LIMIT 1', ':doid', $thisRs->doid ,':thisid',$thisRs->psnid,':toorder',$toRs->printweight);
					//$ret.=mydb()->_query.'<br />';

					if ($toRs->psnid) {
						mydb::query('UPDATE %org_dos% SET `printweight`=:thisorder WHERE `doid`=:doid AND `psnid` = :psnid LIMIT 1',':doid', $thisRs->doid, ':psnid', $toRs->psnid,':thisorder',$thisRs->printweight);
						//$ret.=mydb()->_query.'<br />';
					}
					$ret .= 'Move up completed.';
				} else {
					$ret .= 'Item is already at top.';
				}
			}
			break;

		case 'movedown':
			$doid = $doingInfo->doid;
			$thisRs = mydb::select('SELECT * FROM %org_dos% WHERE `doid` = :doid AND `psnid` = :psnid LIMIT 1', ':doid', $doid, ':psnid',$tranId);

			$toRs = mydb::select('SELECT `psnid`,`printweight` FROM %org_dos% tr WHERE `doid` = :doid AND `printweight` > :thisweight ORDER BY `printweight` ASC LIMIT 1',':doid',$thisRs->doid, ':thisweight',$thisRs->printweight);
			//$ret .= mydb()->_query.'<br />';
			//$ret .= 'This order ='.$thisRs->printweight.' TO '.$toRs->printweight.'<br />'.print_o($toRs,'$toRs');

			if ($thisRs->printweight && $toRs->printweight) {
				mydb::query('UPDATE %org_dos% SET `printweight` = :toorder WHERE `doid` = :doid AND `psnid` = :thisid LIMIT 1', ':doid', $thisRs->doid ,':thisid',$thisRs->psnid,':toorder',$toRs->printweight);
				//$ret.=mydb()->_query.'<br />';

				mydb::query('UPDATE %org_dos% SET `printweight`=:thisorder WHERE `doid`=:doid AND `psnid` = :psnid LIMIT 1',':doid', $thisRs->doid, ':psnid', $toRs->psnid,':thisorder',$thisRs->printweight);
				//$ret.=mydb()->_query.'<br />';
				$ret .= 'Move down completed.';
			} else {
				$ret .= 'Item is already at bottom.';
			}
			break;

		default:
			//if (empty($action)) $action='home';
			//$ret = R::Page('project.join.'.$action, $self, $projectInfo, $calendarInfo, $action, $tranId);

			if (empty($projectInfo)) $projectInfo = $tpid;

			$args = func_get_args();
			$argIndex = 4; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'project.join.'.$action,
				$self,
				$projectInfo,
				$args[$argIndex],
				$args[++$argIndex],
				$args[++$argIndex],
				$args[++$argIndex],
				$args[++$argIndex]
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			break;
	}

	//$ret .= print_o($doingInfo, '$doingInfo');
	//$ret .= print_o($projectInfo, '$projectInfo');
	//$ret .= print_o($calendarInfo, '$calendarInfo');

	return $ret;
}
?>