<?php
function admin_user_roles_create($self) {
	if ($rolename=post('rolename')) {
		$roles=cfg('roles');
		$roles->{$rolename}='';
		cfg_db('roles',$roles);
	}
	location('admin/user/roles');
}
?>