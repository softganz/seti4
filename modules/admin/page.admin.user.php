<?php
/**
* Admin :: User Management
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage admin/user
*/

import('widget:admin.menu.user.php');

class AdminUser extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'User Management',
				'trailing' => '<form id="search" class="search-box" method="get" action="'.url('admin/user/list').'" name="memberlist" role="search">'
					. '<input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="20" value="'.$_GET['q'].'" data-query="'.url('admin/get/username').'" data-altfld="sid" data-callback="submit" placeholder="Username or Name or Email"><button><i class="icon -material">search</i></button>'
					. '</form>',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
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