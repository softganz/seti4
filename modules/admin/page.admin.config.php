<?php
function admin_config($self) {
	$self->theme->title='Site Configuration';
	$ret.='<div class="admin-panel">';
	$ret.='<div class="body">';
	$ret.=R::View('admin.menu.config');
	$ret.='</div>';
	$ret.='</div>';
	return $ret;
}
?>