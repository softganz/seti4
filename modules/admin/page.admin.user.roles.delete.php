<?php
function admin_user_roles_delete($self,$rolename) {
	if (\SG\confirm() && $rolename) {
		$roles=cfg('roles');
		unset($roles->{$rolename});
		cfg_db('roles',$roles);
	}
	location('admin/user/roles');
	return $ret;
}
?>