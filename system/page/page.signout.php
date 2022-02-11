<?php
import('model:user.php');

function signout() {
	R::Model('watch.log'.'user','Signout','user '.$username.' was signout');
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