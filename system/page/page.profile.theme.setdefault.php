<?php
function profile_setdefault($self,$name) {
	//set_theme($name);
	//cfg_db('theme.name',$name);
	$ret .= message('status','Set theme '.$name.' as a default theme');
	$ret .= R::Page('profile.theme');
	return $ret;
}
?>