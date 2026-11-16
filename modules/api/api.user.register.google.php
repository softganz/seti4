<?php
/**
 * API 		  :: User Rergister by Google Account
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-07-17
 * Modified :: 2026-11-16
 * Version	:: 4
 *
 * @uses api/user/register/google
 */

class UserRegisterGoogleApi extends Page {
	public $name;
	public $email;
	public $googleToken;

	function __construct() {
		$this->name = post('name');
		$this->email = post('email');
		$this->googleToken = post('googleToken');
	}

	function build() {
		$jwt = Jwt::isValid($this->googleToken);

		if (!$jwt->payload->email) {
			return (object) [
				'responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE,
				'text' => 'ข้อมูลสำหรับลงทะเบียนไม่ถูกต้อง'
			];
		} elseif (\UserModel::get(email: $jwt->payload->email)) {
			return (object) [
				'responseCode' => _HTTP_ERROR_NOT_ALLOWED,
				'text' => 'อีเมล์นี้มีผู้อื่นสมัครไว้แล้ว ไม่สามารถสมัครซ้ำได้'
			];
		}

		$createUserResult = UserModel::externalUserCreate([
			'prefix' => 'google-',
			'name' => $this->name,
			'email' => $jwt->payload->email,
			'signin' => true,
			'token' => $this->googleToken
		]);

		// print_o($createUserResult, '$createUserResult', 1);
		if (!$createUserResult->userId) {
			return (object) [
				'responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE,
				'text' => 'ไม่สามารถสร้างสมาชิกตามข้อมูลที่ระบุได้'
			];
		}

		return (object) [
			'userId' => $createUserResult->userId,
			'username' => $createUserResult->username,
			'name' => $createUserResult->name,
			'email' => $createUserResult->email
			// 'createUserResult' => $createUserResult,
			// 'post' => post(),
		];
	}
}
?>
