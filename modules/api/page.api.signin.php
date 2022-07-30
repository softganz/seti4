<?php
/**
* API :: User Sign In
* Created 2021-06-21
* Modify  2021-06-21
*
* @return JSON
*
* @usage api/signin
*/

import('model:user.php');

class ApiSignin extends PageApi {
	var $username;
	var $password;
	var $appId;
	var $appToken;
	var $token;
	var $email;

	function __construct() {
		$this->username = $_SERVER['HTTP_USERNAME'];
		$this->password = $_SERVER['HTTP_PASSWORD'];
		$this->appId = $_SERVER['HTTP_APPID'];
		$this->appToken = $_SERVER['HTTP_APPTOKEN'];
		$this->token = $_SERVER['HTTP_SIGNINTOKEN'];
		$this->email = $_SERVER['HTTP_SIGNINEMAIL'];
		// Old version use post('user') && post('pw')
	}

	function build() {
		// if (function_exists("apache_request_headers")) {
		// 	$headers = apache_request_headers();
		// 	$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
		// }

		$result = (Object) [
			'signed' => NULL,
			'status' => NULL,
			'username' => $this->username,
			'password' => $this->password,
			// "headerError" => "Text",
			// "descriptionError" => "Text"
			// 'server' => $_SERVER,
			// 'this' => $this,
			// 'headers' => $headers,
		];

		$user = NULL;

		if ($this->username && $this->password) {
			// debugMsg('SIGN');
			$user = UserModel::signInProcess($this->username, $this->password, -1);
			// $result->user = $user;
			// debugMsg($result, '$result');
			// $user = i();
			// $json->result = $result;
		// } else if ($token = post('token')) {
		// 	$userCache = Cache::get('user:'.$token);
		// 	if ($userCache->count()) {
		// 		$user = $userCache->data;
		// 	}
		} else {
			$user = i();
		}

		$result->signed = $user->ok ? true : false;
		$result->token = NULL;

		if ($user->ok) {
			$result->status = 'complete';
			$result->name = $user->name;
			$result->token = $user->session;

			// $result->user = i();
		} else {
			$result->status = 'fail';
			$result->code = _HTTP_ERROR_UNAUTHORIZED;
			$result->text = 'Sign In Error';
		}

		return $result;
	}
}
?>