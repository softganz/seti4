<?php
function admin_site($self) {
	$self->theme->title='Site building';
	$ret.='<div class="admin-panel">';
	$ret.='<div class="body">';
	$ret.=R::View('admin.menu.site');
	$ret.='</div>';
	$ret.='</div>';
	return $ret;
}
?>