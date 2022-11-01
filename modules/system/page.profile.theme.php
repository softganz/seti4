<?php
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
?>