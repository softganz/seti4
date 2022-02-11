<?php
function admin($self) {
	$self->theme->title = 'Web Site Administrator '.$self->version().' on '.cfg('core.version');

	if (!mydb::table_exists('%variable%')) {
		$self->theme->title = 'First time installation database';
		location('admin/install');
	}

	$ret .= '<div class="help">Welcome to the administration section. Here you may control how your site functions.</div>';

	$ret .= '<p>Core folder <b>'.cfg('core.version').'@'.cfg('core.folder').'</b></p>';
	$ret .= '<p><em>Today is <strong>'.date('Y-m-d H:i:s').'</strong> and server timezone offset is <strong>'.cfg('server.timezone.offset').' hours</strong> so datetime to use by program is <strong>????-??-??</strong></em></p>';
	$ret .= (cfg('version.install') < cfg('core.version.install')?'<p>New version was release. Please <a href="'.url('admin/site/upgrade').'">upgrade database table</a>.</p>':'');

	$menuList = 'content,site,user,config,log,help';

	$ret .= '<div class="admin-panel">';
	foreach (explode(',', $menuList) as $menuItem) {
		$ret .= '<div class="body">';
		$ret .= R::View('admin.menu.'.$menuItem);
		$ret .= '</div>';
	}
	$ret .= '</div>';
	return $ret;
}
?>
