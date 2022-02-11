<?php
/**
* Garage :: Admin Setting
* Created 2020-07-23
* Modify  2020-07-23
*
* @param Object $self
* @return String
*/

$debug = true;

function garage_admin_setting($self) {
	new Toolbar($self,'Garage Settings');
	$self->theme->sidebar = R::View('garage.admin.menu');

	$ret .= '<header class="header"><h3>Garage Settings</h3></header>';

	$ui = new Ui(NULL,'ui-menu');


	$ui->add('<a href="'.url('garage/admin/upgrade').'"><i class="icon -material">build_circle</i><span>Upgrade Database</span></a>');
	$ret .= $ui->build();
	return $ret;
}
?>