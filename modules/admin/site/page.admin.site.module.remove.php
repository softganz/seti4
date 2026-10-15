<?php
function admin_site_module_remove($self,$module) {
	$self->theme->title='Site Modules';
	$ret .= '<div class="help">Remove site modules</div>';

	if (\SG\confirm() && $module) {
		$perm=cfg('perm');
		$remove_perm=$perm->{$module};
		unset($perm->{$module});
		$roles=cfg('roles');
		foreach (explode(',',$remove_perm) as $permItem) {
			foreach ($roles as $rk=>$rv) {
				$rv=str_replace($permItem,'',$rv);
				$rv=str_replace(',,',',',$rv);
				$roles->$rk=$rv;
			}
		}
		cfg_db('perm',$perm);
		cfg_db('roles',$roles);
	}
	location('admin/site/module');
}
?>