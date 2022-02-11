<?php
function admin_content($self) {
	$self->theme->title='Content management';
	$ret.='<div class="admin-panel">';
	$ret.='<div class="body">';
	$ret.=R::View('admin.menu.content');
	$ret.='</div>';
	$ret.='</div>';
	return $ret;
}
?>