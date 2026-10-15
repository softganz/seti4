<?php
/**
* Admin   :: User Management
* Created :: 2016-11-08
* Modify  :: 2024-08-19
* Version :: 2
*
* @return Widget
*
* @usage admin/user
*/

import('widget:admin.menu.user.php');

class AdminUser extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'User Management'
			]), // AdminAppBarWidget
			'body' => new Container([
				'class' => 'admin-panel',
				'children' => [
					new AdminMenuUserWidget(),
				], // children
			]), // Widget
		]);
	}
}
?><?php
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