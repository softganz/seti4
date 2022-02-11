<?php
function flood_admin_setting($self) {
	$self->theme->title='Setting';
	$self->theme->sidebar=R::Page('flood.admin.menu','setting');

	$ret.='<h3>กำหนดค่าระบบ</h3>';

	return $ret;
}
?>