<?php
function profile_theme_select($self,$name) {
	set_theme($name);
	setcookie('theme',$name,time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	return R::Page('profile.theme');
}
?>