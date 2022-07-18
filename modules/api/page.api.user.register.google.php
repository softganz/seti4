<?php
/**
* Module :: Description
* Created 2022-07-17
* Modify  2022-07-17
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

class ApiUserRegisterGoogle extends Page {
	var $name;
	var $email;
	var $googleToken;

	function __construct() {
		$this->name = post('name');
		$this->email = post('email');
		$this->googleToken = post('googleToken');
	}

	function build() {
		$jwt = Jwt::isValid($this->googleToken);

		if (!$jwt->payload->email) return (Object) ['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลสำหรับลงทะเบียนไม่ถูกต้อง'];
		else if (UserModel::get(['email' => $jwt->payload->email])) {
			return (Object) ['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'อีเมล์นี้มีผู้อื่นสมัครไว้แล้ว ไม่สามารถสมัครซ้ำได้'];
		}

		do {
			$username = SG\uniqid(20);
		} while (UserModel::get(['username' => $username]));

		$createUserResult = UserModel::create([
			'username' => $username,
			'password' => NULL,
			'name' => $this->name,
			'email' => $jwt->payload->email,
		]);

		if (!$createUserResult->uid) {
			return (Object) ['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ไม่สามารถสร้างสมาชิกตามข้อมูลที่ระบุได้'];
		}

		$result = (Object) [
			'userId' => $createUserResult->uid,
			'username' => $createUserResult->username,
			'name' => $createUserResult->name,
			'email' => $createUserResult->email,
			// 'createUserResult' => $createUserResult,
			// 'post' => post(),
		];

		// Process User Sign In
		if ($result->useId) {
			$result->signin = UserModel::externalSignIn([
				'email' => $this->email,
				'token' => $this->googleToken,
			]);
		}
		return $result;
	}
}
?>