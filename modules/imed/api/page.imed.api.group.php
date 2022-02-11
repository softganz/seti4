<?php
/**
* iMed API :: Group API
* Created 2021-07-17
* Modify  2021-08-29
*
* @param Int $orgId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage imed/api/group/{id}/{action}[/{tranId}]
*/

$debug = true;

import('model:imed.group');

class ImedApiGroup extends Page {
	var $orgId;
	var $action;
	var $tranId;
	var $orgInfo;

	function __construct($orgId, $action = NULL, $tranId = NULL) {
		$this->orgInfo = ImedGroupModel::get($orgId);
		$this->orgId = $this->orgInfo->orgId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg('Id '.$this->orgId.' Action = '.$this->action.' TranId = '.$this->tranId);
		// debugMsg($this->orgInfo, '$orgInfo');

		$orgId = $this->orgId;
		$tranId = $this->tranId;

		if (empty($orgId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		$isAdmin = $this->orgInfo->RIGHT & _IS_ADMIN;
		// $isEdit = $mainInfo->RIGHT & _IS_EDITABLE;
		$isMember = $this->orgInfo->is->socialtype;
		$isAccess = $isAdmin || $isMember;

		if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text', 'access denied']);

		$ret = '';

		switch ($this->action) {
			// case 'foo': $ret = 'Foo'; break;
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
				if ($psnId = post('psnid')) {
					$psnInfo = R::Model('imed.patient.get',$psnId);
					// TODO: Who is add patient into group _IS_EDITABLE or _IS_ACCESS
					if ($psnInfo->RIGHT & _IS_ACCESS) {
						//$ret .= print_o($psnInfo);
						$data = new stdClass;
						$data->orgid = $orgId;
						$data->psnid = post('psnid');
						$data->addby = i()->uid;
						$data->created = date('U');
						$stmt = 'INSERT INTO %imed_socialpatient% (`orgid`, `psnid`, `addby`, `created`) VALUES (:orgid, :psnid, :addby, :created) ON DUPLICATE KEY UPDATE `orgid` = :orgid';
						mydb::query($stmt, $data);
						// debugMsg(mydb()->_query);
						$ret .= 'เพิ่มเข้ากลุ่มเรียบร้อย';
					}
				}
				break;

			case 'patient.remove':
				if ($tranId && SG\confirm()) {
					mydb::query('DELETE FROM %imed_socialpatient% WHERE `orgid` = :orgid AND `psnid` = :psnid LIMIT 1', ':orgid', $orgId, ':psnid', $tranId);
				}
				break;

			default:
				$ret = message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>