<?php
/**
* Green :: API For Sign In From Firebase
* Created 2022-06-18
* Modify  2022-06-18
*
* @return Widget
*
* @usage green/api/signin/firebase
*/

class SigninFirebase extends Page {
	var $appId;
	var $appToken;
	var $token;
	var $email;

	function __construct() {
		$this->appId = $_SERVER['HTTP_APPID'];
		$this->appToken = $_SERVER['HTTP_APPTOKEN'];
		$this->token = $_SERVER['HTTP_SIGNINTOKEN'];
		$this->email = $_SERVER['HTTP_SIGNINEMAIL'];
	}

	function build() {
		$appList = ['H94Ko' => 'iGreenSmile'];
		$appName = $appList[$this->appId];

		$encryptAppToken = hash('sha256', $this->email.$appName);

		// debugMsg(hash('sha256', 'demo@communeinfo.comiGreenSmile'));
		// debugMsg($encryptAppToken);
		// debugMsg($this->appToken);

		if (!$appName) return (Object) ['status' => 'fail', 'code' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'App Access Denied'];
		else if ($this->appToken != $encryptAppToken) return (Object) ['status' => 'fail', 'code' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Invalid App Token'];

		// if (!in_array($this->appToken, ['Dksid83kdjEujfdjIeldOPf9sld3dSkdk'])) return (Object) ['status' => 'fail', 'code' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'App Access Denied'];

		$result = (Object) [
			'status' => 'complete',
			'userId' => NULL,
			'userName' => NULL,
			'name' => NULL,
			// 'headerError' => 'Text',
			// 'descriptionError' => 'Text'
			'role' => '',
		];

		$user = UserModel::externalSignIn([
			'email' => $this->email,
			'token' => $this->token
		]);

		if ($user->ok) {
			$result->userId = $user->uid;
			$result->userName = $user->username;
			$result->name = $user->name;
		} else {
			$result = (Object) [
				'status' => 'fail',
				'code' => $user->code,
				'text' => $user->text,
			];
		}

		$result->sha = hash('sha256', 'demo@communeinfo.comiGreenSmile');
		$result->en = $encryptAppToken;
		$result->user = $user;

		return $result;
	}
}
?>