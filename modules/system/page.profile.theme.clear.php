<?php
function profile_theme_clear($self) {
	$self->theme->title='Theme clear';
	user_menu('home','home',url());
	user_menu('theme','theme',url('profile/theme'));
	$self->theme->navigator=user_menu();

	setcookie('theme',null,time()-365,cfg('cookie.path'),cfg('cookie.domain'));
	setcookie('style',null,time()-365,cfg('cookie.path'),cfg('cookie.domain'));
	
	if (isset($_COOKIE['theme']) || isset($_COOKIE['style'])) location('profile/theme/clear');

	$ret .= message('status','Clear theme setting:Current theme was reset to default');

	return $ret;
}
?>