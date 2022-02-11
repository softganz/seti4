<?php
function admin_site_theme_clear() {
	$self->theme->title='Theme clear';

	setcookie('theme',null,time()-365,cfg('cookie.path'),cfg('cookie.domain'));
	setcookie('style',null,time()-365,cfg('cookie.path'),cfg('cookie.domain'));

	if (isset($_COOKIE['theme']) || isset($_COOKIE['style'])) location('admin/site/theme/clear');

	cfg_db_delete('theme.name');
	cfg_db('theme.name','default');

	$ret .= notify('Clear theme setting. Current theme was reset to <strong>'.cfg('theme.name').'.</strong>');
	location('admin/site/theme');

	return $ret;
}
?>