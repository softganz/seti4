<?php
function r_signout() {
	if (!i()->ok) return;

	model::watch_log('user','Signout','user '.i()->username.' was signout');
	model::user_clear();
	Cache::Clear('user:'.$_COOKIE[cfg('cookie.id')]);
	setcookie(cfg('cookie.id'),"",time() - 3600,cfg('cookie.path'),cfg('cookie.domain'));
	setcookie(cfg('cookie.u'),"",time() - 3600,cfg('cookie.path'),cfg('cookie.domain'));
	unset($_SESSION['logas']);

	session_unset();
	session_destroy();
}
?>