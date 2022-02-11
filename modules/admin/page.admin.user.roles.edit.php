<?php
function admin_user_roles_edit($self,$rolename) {
	$role=$para->edit;
	$roles=cfg('roles');
	if (!isset($roles->$rolename)) return message('error','Role '.$rolename.' not exists.');

	if ($_POST) {
		$post=post('role',_TRIM);
		$roles->$rolename=$post['description'];
		cfg_db('roles',$roles);
		location('admin/user/roles');
	}
	$ret .= '<h3>Role '.$rolename.'</h3>';


	$form=new Form('role',url(q()),'edit-role');

	$form->addField(
		'description',
		array(
			'type'=>'textarea',
			'label'=>'บทบาท :',
			'rows'=>10,
			'class'=>'-fill',
			'value'=>$roles->$rolename
		)
	);

	$form->addField(
		'fieldname',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>Save Role</span>',
			'posttext'=>!in_array($rolename,array('anonymous','member'))?' or <a class="sg-action" href="'.url('admin/user/roles/delete/'.$rolename).'" data-rel="none" data-callback="'.url('admin/user/roles').'" data-confirm="Delete this role. Comfirm?">Delete this role</a>':NULL,
		)
	);

	$ret .= $form->build();
	return $ret;
}
?>