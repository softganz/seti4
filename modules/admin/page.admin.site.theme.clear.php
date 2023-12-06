<?php
/**
* Module  :: Description
* Created :: 2023-12-04
* Modify  :: 2023-12-04
* Version :: 1
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class AdminSiteThemeClear extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		parent::__construct([
			'arg1' => $arg1
		]);
	}

	function build() {
		setcookie('theme',null,time()-365,cfg('cookie.path'),cfg('cookie.domain'));
		setcookie('style',null,time()-365,cfg('cookie.path'),cfg('cookie.domain'));

		if (isset($_COOKIE['theme']) || isset($_COOKIE['style'])) location('admin/site/theme/clear');

		cfg_db_delete('theme.name');
		cfg_db('theme.name','default');

		$ret .= notify('Clear theme setting. Current theme was reset to <strong>'.cfg('theme.name').'.</strong>');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Clear Theme To Default',
			]), // AppBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}
}
?>
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