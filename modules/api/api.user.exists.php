<?php
/**
* User    :: Check User Exists API
* Created :: 2024-02-14
* Modify  :: 2024-02-15
* Version :: 1
*
* @return Array/Object
*
* @usage api/user/exists
*/

use Softganz\DB;

class UserExistsApi extends PageApi {
	var $userId;

	function __construct() {
		parent::__construct([
			'username' => post('name'),
			'email' => post('email'),
		]);
	}

	function build() {
		if ($this->username) return $this->checkUsername();
		else if ($this->email) return $this->checkEmail();
		else return error(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุข้อมูลที่ต้องการตรวจสอบ');
	}

	private function checkUsername() {
		$userInfo = UserModel::get(['username' => $this->username]);
		// debugMsg($userInfo);

		if ($userInfo->userId) {
			return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'มีสมาชิกท่านอื่นใช้ชื่อนี้อยู่แล้ว');
		} else {
			return success('สามารถใช้งานได้');
		}
	}

	private function checkEmail() {
		if (empty($this->email)) return error(_HTTP_OK_NO_CONTENT, 'ไม่ระบุอีเมล์');

		$userInfo = UserModel::get(['email' => $this->email]);
		// debugMsg($userInfo);

		if ($userInfo->userId) {
			return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'มีสมาชิกท่านอื่นใช้อีเมล์นี้อยู่แล้ว');
		} else {
			return success('สามารถใช้งานได้');
		}
	}

}
?>