<?php
function admin_site_theme_select($self,$name) {
	set_theme($name);
	cfg_db('theme.name',$name);
	$ret .= message('status','Set theme '.$name.' as a default theme');
	location('admin/site/theme');
	return $ret;
}
?>