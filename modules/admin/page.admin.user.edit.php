<?php
// edit user information
function admin_user_edit($self,$uid) {
	$rs=R::Model('user.get',$uid);

	if ($rs->_empty) return message('error','User <em>'.$uid.'</em> not exists.');

	if ($_POST['cancel']) location('admin/user/list');

	$profile=(object)post('profile',_TRIM);

	if ($profile->username) {
		//if (empty($profile->email)) $error[]='กรุณาป้อนอีเมล์';
		if ($profile->email && !sg_is_email($profile->email)) $error[]='กรุณาป้อนอีเมล์ให้ถูกต้องตามรูปแบบ คือ yourname@domain.com';

		if ($profile->password) {
			if (strlen($profile->password)<6) $error[]='รหัสผ่านใหม่ต้องตัวอักษรอย่างน้อย 6 อักษร'; //-- password length
			if ($profile->password != $profile->repassword) $error[]='การป้อนรหัสผ่านใหม่ทั้งสองครั้งไม่ตรงกัน'; //-- password <> retype
		}

		if ($error) {
			$message=message('error',$error);
		} else {
			if ($profile->password) {
				$profile->password=sg_encrypt($profile->password,cfg('encrypt_key'));
				unset($profile->repassword);
			} else {
				unset($profile->password,$profile->repassword);
			}
			$profile->roles=implode(',',$profile->roles);
			$oldRoles=mydb::select('SELECT `roles` FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$rs->uid)->roles;

			// Delete cache when block or roles change
			if ($profile->status=='block' || $profile->roles!=$oldRoles) {
				mydb::query('DELETE FROM %cache% WHERE `headers`=:username',':username',$profile->username);
			}

			//unset($profile->username);

			mydb::query(mydb::create_update_cmd('%users%',(array)$profile,' uid='.$rs->uid.' LIMIT 1'));

			//location('admin/user/list');
			return $ret;
		}
	} else {
		$profile=(object)$rs;
	}

	$ret .= '<header class="header -box"><nav class="nav -back -hidden"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>Account information</h3></header>';

	$form=new Form('profile',url(q()),'edit-account','sg-form');
	$form->addData('checkValid',true);
	$form->addData('rel', 'none');
	$form->addData('done', 'close');

	if ($message) $form->addField('message',$message);

	$form->addField(
						'username',
						array(
							'type'=>'text',
							'label'=>'Username',
							'class'=>'-fill',
							'maxlength'=>30,
							'require'=>true,
							'value'=>htmlspecialchars($profile->username),
							'description'=>'Your preferred username; punctuation is not allowed except for periods, hyphens, and underscores.'
							)
						);

	$form->addField(
						'email',
						array(
							'type'=>'text',
							'label'=>'E-mail address',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>htmlspecialchars($profile->email),
							'description'=>'A valid e-mail address. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by e-mail.')
						);

	$form->addField(
						'password',
						array(
							'type'=>'password',
							'label'=>'Password',
							'maxlength'=>20,
							'class'=>'-fill'
							)
						);

	$form->addField(
						'repassword',
						array(
							'type'=>'password',
							'label'=>'Confirm password',
							'class'=>'-fill',
							'maxlength'=>20,
							'description'=>'To change the current user password, enter the new password in both fields.'
							)
						);

	$options=array();
	if ($profile->status=='disable') $options['disable']='Disabled';
	$options['block']='Blocked';
	$options['enable']='Active';
	$options['locked']='Locked';

	$form->addField(
						'status',
						array(
							'type'=>'radio',
							'label'=>'Status :',
							'options'=>$options,
							'value'=>$profile->status,
							'description'=>'To change the user status.'
							)
						);

	$roles=cfg('roles');
	unset($roles->member,$roles->anonymous);
	$options=array();
	foreach (array_keys((array)$roles) as $role) {
		if ($role=='admin' && !(in_array('admin',i()->roles) || i()->uid==1)) continue;
		$options[$role]=$role;
	}

	$form->addField(
						'roles',
						array(
							'type'=>'checkbox',
							'label'=>'Roles :',
							'options'=>$options,
							'value'=>$profile->roles,
							'multiple'=>true,
							'description'=>'The user receives the combined permissions of the <em>authenticated user</em> role, and all roles selected here. For <a href="'.url('admin/user/access/uid/'.$rs->uid).'">additional authenticated for this user only</a>.'
							)
						);

	$form->addField(
					'admin_remark',
					array(
						'type'=>'textarea',
						'label'=>'Admin Remark',
						'class'=>'-fill',
						'rows'=>2,
						'value'=>htmlspecialchars($profile->admin_remark)
						)
					);

	$form->addField(
					'submit',
					array(
						'type'=>'button',
						'items'=>array(
											'remove'=>array(
																'type'=>'text',
																'value'=>'<a class="sg-action btn -link" href="'.url('admin/user/delete/'.$uid).'" data-rel="#main" data-confirm="Delete this user, Are you sure?"><i class="icon -material">delete</i><span>Remove this user</span></a> Or ',
																),
											'cancel'=>array(
																'type'=>'cancel',
																'value'=>'<i class="icon -material">cancel</i><span>Cancel</span>'
																),
											'save'=>array(
																'type'=>'submit',
																'class'=>'-primary',
																'value'=>'<i class="icon -material">done_all</i><span>Save User Information</span>'
																),
											),
							'container' => '{class: "-sg-text-right"}',
						)
					);

	$ret .= $form->build();

	return $ret;
}
?>