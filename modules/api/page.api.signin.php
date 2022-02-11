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

$debug = true;

import('model:user.php');

class ApiSignin extends Page {
	function __construct() {}

	function build() {
		$json = (Object) [];

		$user = NULL;

		if (post('user') && post('pw')) {
			// debugMsg('SIGN');
			$user = UserModel::signInProcess(post('user'),post('pw'),-1);
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

		$json->signed = $user->ok ? true : false;
		$json->token = NULL;

		if ($user->ok) {
			$json->userId = $user->uid;
			$json->name = $user->name;
			$json->username = $user->username;
			$json->token = $user->session;

			// $json->user = i();
		}

		// $json->post = array_slice(post(),1);
		// $json->user = $user;
		// $json->cache = $userCache->data ? $userCache : NULL;
		// $json->i = i();
		// unset($json->i->server);

		return $json;
	}
}
?>