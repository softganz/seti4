<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	if (empty($action) && empty($orgId)) {
		return R::Page('imed.social.home',$self);
	} else if (empty($action) && $orgId) {
		return R::Page('imed.social.info',$self,$orgId);
	}

	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;

	// DO submodule controller
	//R::View('imed.toolbar', $self, 'ศูนย์กายอุปกรณ์', 'pocenter', $orgInfo);

	$ret = '';

	switch ($action) {
		case 'invite.add':
			if ($isAdmin && post('uid')) {
				$data = new stdClass();
				$data->orgid = $orgId;
				$data->uid = post('uid');
				$data->addby = i()->uid;
				$data->inviteByName = i()->name;
				$data->status = 1;
				$data->membership = post('membership');
				$data->created = date('U');

				$bigData = new stdClass();
				$bigData->keyname = 'imed';
				$bigData->keyid = $data->uid;
				$bigData->fldref = $data->orgid;
				$bigData->fldname = 'group.invite';
				$bigData->flddata = SG\json_encode($data);
				$bigData->created = $data->created;
				$bigData->ucreated = i()->addby;
				$stmt = 'INSERT INTO %bigdata% (`keyname`, `keyid`, `fldref`, `fldname`, `flddata`, `created`, `ucreated`) VALUES (:keyname, :keyid, :fldref, :fldname, :flddata, :created, :ucreated)';
				mydb::query($stmt, $bigData);
			}
			break;

		case 'invite.accept':
			if ($orgId) {
				$stmt = 'SELECT `bigid`, `keyid` `uid`, `fldref` `orgid`, `flddata` `data` FROM %bigdata% WHERE `keyname` = "imed" AND `fldname` = "group.invite" AND `keyid` = :uid AND `fldref` = :orgid LIMIT 1';
				$inviteInfo = mydb::select($stmt, ':orgid', $orgId, ':uid', i()->uid);
				if ($inviteInfo->count()) {
					$data = SG\json_decode($inviteInfo->data);
					$stmt = 'INSERT INTO %imed_socialmember%
						(`orgid`, `uid`, `addby`, `membership`, `status`, `created`)
						VALUES
						(:orgid, :uid, :addby, :membership, :status, :created)
						ON DUPLICATE KEY UPDATE
						`addby` = :addby
						, `membership` = :membership
						, `created` = :created';
					mydb::query($stmt, $data);
					//$ret .= mydb()->_query;
					mydb::query('DELETE FROM %bigdata% WHERE `bigid` = :bigid LIMIT 1', ':bigid', $inviteInfo->bigid);
				}
				//$ret .= print_o($inviteInfo);
			}
			break;

		case 'invite.reject':
			if ($orgId) {
				$stmt = 'DELETE FROM %bigdata% WHERE `keyname` = "imed" AND `fldname` = "group.invite" AND `keyid` = :uid AND `fldref` = :orgid LIMIT 1';
				mydb::query($stmt, ':orgid', $orgId, ':uid', i()->uid);
			}
			break;

		case 'invite.remove':
			if ($orgId && $isAdmin && SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `keyname` = "imed" AND `fldname` = "group.invite" AND `keyid` = :uid AND `fldref` = :orgid LIMIT 1';
				mydb::query($stmt, ':orgid', $orgId, ':uid', $tranId);
			}
			break;

			/*
		case 'member.add':
			if ($isAdmin && post('uid')) {
				$data = new stdClass;
				$data->orgid = $orgId;
				$data->uid = post('uid');
				$data->addby = i()->uid;
				$data->inviteByName = i()->name;
				$data->status = 1;
				$data->membership = post('membership');
				$data->created = date('U');

				$bigData = new stdClass();
				$bigData->keyname = 'imed';
				$bigData->keyid = $data->uid;
				$bigData->fldref = $data->orgid;
				$bigData->fldname = 'group.invite';
				$bigData->flddata = SG\json_encode($data);
				$bigData->created = $data->created;
				$bigData->ucreated = i()->addby;
				$stmt = 'INSERT INTO %bigdata% (`keyname`, `keyid`, `fldref`, `fldname`, `flddata`, `created`, `ucreated`) VALUES (:keyname, :keyid, :fldref, :fldname, :flddata, :created, :ucreated)';
				mydb::query($stmt, $bigData);

				//$stmt = 'INSERT INTO %imed_socialmember% (`orgid`, `uid`, `addby`, `membership`, `status`, `created`) VALUES (:orgid, :uid, :addby, :membership, :status, :created) ON DUPLICATE KEY UPDATE `orgid` = :orgid';
				//mydb::query($stmt, $data);
				//$ret .= 'Add member'.print_o(post());
			}
			break;
		*/

		case 'member.mute':
			if ($isAdmin && $tranId) {
				mydb::query('UPDATE %imed_socialmember% SET `status` = -1 WHERE `orgid` = :orgid AND `uid` = :uid LIMIT 1', ':orgid', $orgId, ':uid', $tranId);
			}
			break;

		case 'member.unmute':
			if ($isAdmin && $tranId) {
				mydb::query('UPDATE %imed_socialmember% SET `status` = 1 WHERE `orgid` = :orgid AND `uid` = :uid LIMIT 1', ':orgid', $orgId, ':uid', $tranId);
			}
			break;

		case 'member.remove':
			if ($isAdmin && $tranId && SG\confirm()) {
				mydb::query('DELETE FROM %imed_socialmember% WHERE `orgid` = :orgid AND `uid` = :uid LIMIT 1', ':orgid', $orgId, ':uid', $tranId);
			}
			break;

		case 'member.type':
			if ($isAdmin && $tranId && post('ty')) {
				$stmt = 'UPDATE %imed_socialmember% SET `membership` = :membership WHERE `orgid` = :orgid AND `uid` = :uid LIMIT 1';
				mydb::query($stmt, ':orgid', $orgId, ':uid', $tranId, ':membership', post('ty'));
				//$ret .= mydb()->_query;
			}
			break;

		case 'patient.add':
			if ($isMember && $psnid = post('psnid')) {
				$psnInfo = R::Model('imed.patient.get',$psnid);
				// TODO: Who is add patient into group _IS_EDITABLE or _IS_ACCESS
				if ($psnInfo->RIGHT & (_IS_ACCESS)) {
					//$ret .= print_o($psnInfo);
					$data = new stdClass;
					$data->orgid = $orgId;
					$data->psnid = post('psnid');
					$data->addby = i()->uid;
					$data->created = date('U');
					$stmt = 'INSERT INTO %imed_socialpatient% (`orgid`, `psnid`, `addby`, `created`) VALUES (:orgid, :psnid, :addby, :created) ON DUPLICATE KEY UPDATE `orgid` = :orgid';
					mydb::query($stmt, $data);
					//$ret .= mydb()->_query;
					//$ret .= 'Add member'.print_o(post());
					$ret .= 'เพิ่มเข้ากลุ่มเรียบร้อย';
				} else {
					$ret .= 'ACCESS DENIED';
				}
				//$ret .= print_o($psnInfo);
			} else {
				$ret .= 'Incomplete '.($isMember ? 'MEMBER' : 'NOT MEMBER');
			}
			break;

		case 'patient.remove':
			if ($isMember && $tranId && SG\confirm()) {
				mydb::query('DELETE FROM %imed_socialpatient% WHERE `orgid` = :orgid AND `psnid` = :psnid LIMIT 1', ':orgid', $orgId, ':psnid', $tranId);
			}
			break;

	default:
		$argIndex = 3; // Start argument

		//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
		//$ret .= print_o(func_get_args(), '$args');

		$ret = R::Page(
			'imed.social.'.$action,
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

	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}
?>