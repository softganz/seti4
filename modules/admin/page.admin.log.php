<?php
function admin_log($self) {
	$self->theme->title='Logs';
	$ret.='<div class="admin-panel">';
	$ret.='<div class="body">';
	$ret.=R::View('admin.menu.log');
	$ret.='</div>';
	$ret.='</div>';
	return $ret;
}
?>