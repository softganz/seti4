<?php
/**
* Module  :: Description
* Created :: 2022-12-05
* Modify  :: 2022-12-05
* Version :: 1
*
* @param Int $resvId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

class CalendarRoomInfoApi extends PageApi {
	var $resvId;
	var $action;
	var $tranId;
	var $right;
	var $resvInfo;

	function __construct($resvId, $action, $tranId = NULL) {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			'resvInfo' => $resvInfo = ($resvId ? R::Model('calendar.get.resv',$resvId) : NULL),
			'resvId' => $resvInfo->resvId,
			'right' => (Object) [
				'admin' => is_admin(),
				'edit' => is_admin() || $resvInfo->uid = i()->uid,
			],
		]);
	}

	function build() {
		// debugMsg('resvId '.$this->resvId.' Action = '.$this->action.' TranId = '.$this->tranId);

		if (empty($this->resvId)) {
			return message([
				'code' => _HTTP_ERROR_NOT_FOUND,
				'text' => 'PROCESS ERROR',
			]);
		}

		// if (!$isAccess) return message(['code' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		// else if (!$isEdit) return message(['code' => _HTTP_ERROR_FORBIDDEN, 'text' => 'API Access Denied']);

		return parent::build();
	}

	public function cancel() {
		if (!$this->right->edit) return ['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied'];

		mydb::query(
			'UPDATE %calendar_room% SET `approve` = :approve
			WHERE `resvid` = :resvid
			LIMIT 1',
			[
				':resvid' => $this->resvInfo->resvId,
				':approve' => 'ยกเลิก'
			]
		);

		return;

		if ($_POST && user_access('administer calendar rooms','edit own calendar room content',$resvInfo->uid)) {
			if ($_POST['ยกเลิก']) $approve='ยกเลิก';
			else if ($_POST['ไม่อนุมัติ'] && user_access('administer calendar rooms')) $approve='ไม่อนุมัติ';
			else if ($_POST['อนุมัติ'] && user_access('administer calendar rooms')) $approve='อนุมัติ';
			else if ($_POST['delete']) {
				mydb::query('DELETE FROM %calendar_room% WHERE `resvid`=:resvid LIMIT 1',':resvid',$resvInfo->resvid);
				location('calendar/room');
			}
			if ($approve) {
				mydb::query('UPDATE %calendar_room% SET `approve`=:approve WHERE `resvid`=:resvid LIMIT 1',':resvid',$resvInfo->resvid,':approve',$approve);
				$resvInfo->approve=$approve;
			}
		}
	}

	public function notPass() {
		if (!$this->right->admin) return ['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied'];

		mydb::query(
			'UPDATE %calendar_room% SET `approve` = :approve
			WHERE `resvid` = :resvid
			LIMIT 1',
			[
				':resvid' => $this->resvInfo->resvId,
				':approve' => 'ไม่อนุมัติ'
			]
		);

		return;
	}

	public function pass() {
		if (!$this->right->admin) return ['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied'];

		mydb::query(
			'UPDATE %calendar_room% SET `approve` = :approve
			WHERE `resvid` = :resvid
			LIMIT 1',
			[
				':resvid' => $this->resvInfo->resvId,
				':approve' => 'อนุมัติ'
			]
		);

		return;
	}

	public function delete() {
		if (!$this->right->admin) return ['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied'];

		mydb::query(
			'DELETE FROM %calendar_room% WHERE `resvid` = :resvid LIMIT 1',
			[':resvid' => $this->resvInfo->resvId]
		);

		return;
	}


}
?>