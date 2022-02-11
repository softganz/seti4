<?php
function admin_user($self) {
	$self->theme->title='User Management';
	$ret.='<div class="admin-panel">';
	$ret.='<div class="body">';
	$ret.=R::View('admin.menu.user');
	$ret.='</div>';
	$ret.='</div>';
	return $ret;
}
?>