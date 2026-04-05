<?php
/**
* API     :: User Sign In
* Created :: 2021-06-21
* Modify  :: 2026-04-05
* Version :: 3
*
* @return JSON
*
* @usage api/signin
*/

class SigninApi extends PageApi {
	var $username;
	var $password;
	var $appId;
	var $appToken;
	var $token;
	var $email;
	var $time = -1;

	function __construct() {
		parent::__construct([
			'username' => $_SERVER['HTTP_USERNAME'],
			'password' => $_SERVER['HTTP_PASSWORD'],
			'time' => SG\getFirst($_SERVER['HTTP_TIME'], $this->time),
			'appId' => $_SERVER['HTTP_APPID'],
			'appToken' => $_SERVER['HTTP_APPTOKEN'],
			'token' => $_SERVER['HTTP_SIGNINTOKEN'],
			'email' => $_SERVER['HTTP_SIGNINEMAIL'],
		]);
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
			'token' => NULL,
			'code' => NULL,
			'text' => NULL,
		];

		$user = NULL;

		if ($this->username && $this->password) {
			$user = UserModel::signInProcess($this->username, $this->password, $this->time);
		}

		$result->signed = $user->ok ? true : false;
		$result->token = NULL;

		if ($user->ok) {
			$result->status = 'complete';
			$result->name = $user->name;
			$result->token = $user->session;
			$result->roles = $user->roles;
		} else {
			http_response_code(_HTTP_ERROR_UNAUTHORIZED);
			$result->status = 'fail';
			$result->code = _HTTP_ERROR_UNAUTHORIZED;
			$result->text = 'Sign In Error';
		}

		return $result;
	}
}
?>