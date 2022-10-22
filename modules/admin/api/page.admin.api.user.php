<?php
/**
* Admin   :: Admin User API
* Created :: 2022-10-22
* Modify  :: 2022-10-22
* Version :: 1
*
* @param Int $userId
* @param String $action
* @return String
*
* @usage admin/api/user/{userId}/{action}
*/

class AdminApiUser extends PageApi {
	var $userId;
	var $action;

	function __construct($userId, $action) {
		parent::__construct([
			'action' => $action,
			'userInfo' => $userId ? UserModel::get($userId) : NULL,
		]);
		$this->userId = $this->userInfo->uid;
	}

	/**
	* Block/UnBlock User
	*/
	function block() {
		if (!$this->userId || !SG\confirm()) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_BAD_REQUEST,
				'ข้อมูลไม่ครบถ้วน',
			]);
		}

		$status = $this->userInfo->status == 'block' ? 'enable' : 'block';

		// Delete cache when block or roles change
		mydb::query(
			'UPDATE %users% SET `status` = :status WHERE `uid` = :uid LIMIT 1',
			[
				':uid' => $this->userId,
				':status' => $status,
			]
		);

		mydb::query(
			'DELETE FROM %cache% WHERE `headers` = :username',
			[ ':username' => $this->userInfo->username ]
		);

		R::model('watchdog.log','Admin','User '.($status == 'block' ? 'Block' : 'Active'),'User '.$uid.' ('.$this->userInfo->username.') was '.($status == 'block' ? 'blocked' : 'active').'.', i()->uid, $uid);

		return notify('User '.$username.' was '.($status == 'block' ? 'blocked' : 'active').'.');
	}

	/**
	* Block user and delete all topic
	*/
	public function BlockAndDelete() {
		import('model:node.php');

		if (!$this->userId) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_BAD_REQUEST,
				'text' => 'ไม่มีข้อมูลสมาชิก',
			]);
		} else if (!SG\confirm()) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_BAD_REQUEST,
				'text' => 'กรุณายืนยัน',
			]);
		}

		mydb::query(
			'UPDATE %users% SET `status` = "block" WHERE `uid` = :uid LIMIT 1',
			[':uid' => $this->userId]
		);

		mydb::query(
			'DELETE FROM %cache% WHERE `headers` = :username',
			[':username' => $this->userInfo->username]
		);

		$dbs = mydb::select(
			'SELECT `tpid`, `type`, `title`, `created`, `view`, `reply`, `last_reply`
			 FROM %topic%
			 WHERE `uid` = :uid
			 ORDER BY `created` DESC',
			 [':uid' => $this->userId]
		);
		// debugMsg($dbs,'$dbs');

		// Delete node
		foreach ($dbs->items as $rs) {
			if (in_array($rs->type, ['story', 'page', 'forum'])) {
				$nodeDeleteResult = NodeModel::delete($rs->tpid);
				if ($nodeDeleteResult->complete) {
					$ret .= 'Topic '.$rs->tpid.' DELETED<br />';
				}
			}
		}

		R::model('watchdog.log','Admin','User Block','User '.$this->userId.' was blocked and delete topics.', i()->uid, $this->userId);

		return 'Blocked and delete '.$dbs->_num_rows.' topics';
	}
}
?>