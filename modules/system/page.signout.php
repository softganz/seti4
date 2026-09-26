<?php
/**
 * signout  :: User sign out
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-12-12
 * Modified :: 2026-09-26
 * Version  :: 3
 *
 * @param String $args
 * @return Widget
 *
 * @uses project/helpcenter
 */

import('model:user.php');

function signout() {
	LogModel::save([
		'module' => 'user',
		'keyword' => 'Sign Out',
		'message' => 'User '.i()->username.' was signout',
	]);

	UserModel::signOutProcess();

	$ret = isset($_GET['ret_url']) ? $_GET['ret_url'] : $_SERVER['HTTP_REFERER'];

	location($ret);
}
?>