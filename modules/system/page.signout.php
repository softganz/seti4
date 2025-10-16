<?php
/**
* signout :: User sign out
* Created :: 2021-12-12
* Modify  :: 2025-06-15
* Version :: 2
*
* @param String $args
* @return Widget
*
* @usage project/helpcenter
*/

import('model:user.php');

function signout() {
	LogModel::save([
		'module' => 'user',
		'keyword' => 'Sign Out',
		'message' => 'User '.i()->username.' was signout',
	]);

	UserModel::clearLogin();
	Cache::Clear('user:'.$_COOKIE[cfg('cookie.id')]);

	setcookie(cfg('cookie.id'),"",time() - 3600,cfg('cookie.path'),cfg('cookie.domain'));
	setcookie(cfg('cookie.u'),"",time() - 3600,cfg('cookie.path'),cfg('cookie.domain'));

	unset($_SESSION['logas']);

	$ret = isset($_GET['ret_url']) ? $_GET['ret_url'] : $_SERVER['HTTP_REFERER'];

	session_unset();
	session_destroy();

	location($ret);
}
?>