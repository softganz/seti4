<?php
function view_admin_menu_user() {
	$ret='<h3><a href="'.url('admin/user').'">User Management</a></h3>';
	$ret.='<dl class="admin-list">
<dt><a href="'.url('admin/user/access').'">Access control</a></dt><dd>User can access each of module.</dd>
<dt><a href="'.url('admin/user/rules').'">Access rules</a></dt><dd>Rules.</dd>
<dt><a href="'.url('admin/user/create').'">Create users</a></dt><dd>Create many users in one click.</dd>
<dt><a href="'.url('admin/user/roles').'">Roles</a></dt><dd>Roles for each user group.</dd>
<dt><a href="'.url('admin/user/list').'">Users</a></dt><dd>All user listing.</dd>
<dt><a href="'.url('admin/user/setting').'">User settings</a></dt><dd>Setting for user.</dd>
</dl>';
	return $ret;
}
?>