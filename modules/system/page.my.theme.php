<?php
/**
* My      :: Theme Management
* Created :: 2024-07-23
* Modify  :: 2024-07-23
* Version :: 1
*
* @param String $arg1
* @return Widget
*
* @usage my/theme
*/

class MyTheme extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		parent::__construct([
			'arg1' => $arg1
		]);
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Title',
			]), // AppBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}
}

// Old vesion from profile
function profile_theme($self) {
	$self->theme->title='Theme select';
	user_menu('home','home',url());
	user_menu('theme','theme',url('profile/theme'));
	$self->theme->navigator=user_menu();

	$ret .= '<p><strong>Please select new theme or <a href="'.url('profile/theme/clear').'">Restore theme to default</a></strong></p>';
	$theme_folder=cfg('folder.abs').cfg('theme.folder');

	$d = dir($theme_folder);
	while (false !== ($entry = $d->read())) {
		if ( in_array($entry,array('.','..')) ) continue;
		if (!is_dir($theme_folder.'/'.$entry)) continue;
		$themes[] = $entry;
	}
	asort($theme_folder);
	$d->close();

	$ret .= '<ul>';
	foreach ($themes as $theme) {
		$theme_thumbnail_file=$theme_folder.'/'.$theme.'/theme.thumbnail.png';
		$theme_thumbnail=_URL.cfg('theme.folder').'/'.$theme.'/theme.thumbnail.png';
		$ret.='<li><h3>Theme name : '.$theme.'</h3>';
		$ret.=(file_exists($theme_thumbnail_file)?'<a href="'.url('profile/theme/select/'.$theme).'"><img src="'.$theme_thumbnail.'" alt="" /></a>':'No theme photo.');
		$ret.='<p><a href="'.url('profile/theme/select/'.$theme).'">Select</a>';
		if (user_access('access administrator pages')) $ret.=' | <a href="'.url('profile/theme/setdefault/'.$theme).'">Set as default</a>';
		$ret.='</p></li>';
	}
	$ret.='</ul>';

	return $ret;
}

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

function profile_theme_select($self,$name) {
	set_theme($name);
	setcookie('theme',$name,time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	return R::Page('profile.theme');
}

function profile_setdefault($self,$name) {
	//set_theme($name);
	//cfg_db('theme.name',$name);
	$ret .= message('status','Set theme '.$name.' as a default theme');
	$ret .= R::Page('profile.theme');
	return $ret;
}

?>