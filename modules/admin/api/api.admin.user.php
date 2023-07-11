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
* @usage api/admin/user/{userId}/{action}
*/

import('model:user.php');

class AdminUserApi extends PageApi {
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
		if (!$this->userId || !\SG\confirm()) {
			return [
				'responseCode' => _HTTP_ERROR_BAD_REQUEST,
				'ข้อมูลไม่ครบถ้วน',
			];
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

		return 'User '.$username.' was '.($status == 'block' ? 'blocked' : 'active').'.';
	}

	/**
	* Block user and delete all topic
	*/
	public function blockAndDelete() {
		import('model:node.php');

		if (!$this->userId) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_BAD_REQUEST,
				'text' => 'ไม่มีข้อมูลสมาชิก',
			]);
		} else if (!\SG\confirm()) {
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

	public function edit() {
		$data = (Object) post('profile');
		// debugMsg($data, '$data');
		// debugMsg($this, '$this');

		// Check duplicate username
		if ($data->username != $this->userInfo->username) {
			$newUser = UserModel::get(['username' => $data->username]);
			if ($newUser->userId) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'Username '.$data->username.' มีผู้อื่นใช้งานแล้ว');
		}

		if ($data->email && !sg_is_email($data->email)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'กรุณาป้อนอีเมล์ให้ถูกต้องตามรูปแบบ คือ yourname@domain.com');

		if ($data->password) {
			// Check password length
			if (strlen($data->password) < 6) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'รหัสผ่านใหม่ต้องตัวอักษรอย่างน้อย 6 อักษร');
			// Check password same
			if ($data->password != $data->repassword) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'การป้อนรหัสผ่านใหม่ทั้งสองครั้งไม่ตรงกัน');
		}

		if ($data->password) {
			$data->password = sg_encrypt($data->password,cfg('encrypt_key'));
			unset($data->repassword);
		} else {
			unset($data->password,$data->repassword);
		}
		$data->roles = implode(',', $data->roles ? $data->roles : []);
		$oldRoles = mydb::select(
			'SELECT `roles` FROM %users% WHERE `uid` = :uid LIMIT 1',
			[':uid' => $this->userId]
		)->roles;
		// Delete cache when block or roles change
		if ($data->status == 'block' || $data->roles != $oldRoles) {
			mydb::query(
				'DELETE FROM %cache% WHERE `headers` = :username',
				[':username' => $this->userInfo->username]
			);
		}

		// Change username
		if ($data->username != $this->userInfo->username) UserModel::changeUserName($this->userInfo->username, $data->username);

		mydb::query(mydb::create_update_cmd('%users%', (Array) $data,' uid = '.$this->userId.' LIMIT 1'));

		return message('บันทึกเรียบร้อย');
	}
}
?>