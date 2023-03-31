<?php
/**
* Admin   :: Edit User Information
* Created :: 2023-03-31
* Modify  :: 2023-03-31
* Version :: 2
*
* @param String $userId
* @return Widget
*
* @usage admin/user/{id}/edit
*/

class AdminUserEdit extends Page {
	var $userId;
	var $userInfo;

	function __construct($userId = NULL) {
		parent::__construct([
			'userInfo' => $userInfo = is_numeric($userId) ? UserModel::get($userId) : NULL,
			'userId' => $userInfo->userId,
		]);
	}

	function build() {
		if (empty($this->userId)) return error(_HTTP_ERROR_BAD_REQUEST,'User <em>'.$this->userId.'</em> not exists.');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Edit User Information',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Form([
				'variable' => 'profile',
				'action' => url('api/admin/user/'.$this->userId.'/edit'),
				'id' => 'edit-account',
				'class' => 'sg-form',
				'checkValid' => true,
				'rel' => 'notify',
				'done' => 'close | load',
				'children' => [
					// if ($message) $form->addField('message',$message);
					'username' => [
						'type'=>'text',
						'label'=>'Username',
						'class'=>'-fill',
						'maxlength'=>30,
						'require'=>true,
						'value'=>$this->userInfo->username,
						'description'=>'Your preferred username; punctuation is not allowed except for periods, hyphens, and underscores.',
					],
					'email' => [
						'type'=>'text',
						'label'=>'E-mail address',
						'class'=>'-fill',
						'maxlength'=>50,
						'value'=>$this->userInfo->email,
						'description'=>'A valid e-mail address. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by e-mail.',
					],
					'password' => [
						'type'=>'password',
						'label'=>'Password',
						'maxlength'=>20,
						'class'=>'-fill',
					],
					'repassword' => [
						'type'=>'password',
						'label'=>'Confirm password',
						'class'=>'-fill',
						'maxlength'=>20,
						'description'=>'To change the current user password, enter the new password in both fields.',
					],
					'status' => [
						'type'=>'radio',
						'label'=>'Status :',
						'options' => [
							// if ($this->userInfo->status=='disable') $options['disable']='Disabled';
							'disable' => 'Disabled',
							'block' => 'Blocked',
							'enable' => 'Active',
							'locked' => 'Locked',
						],
						'value'=>$this->userInfo->status,
						'description'=>'To change the user status.',
					],
					'roles' => [
						'type'=>'checkbox',
						'label'=>'Roles :',
						'options' => (function() {
							$roles = cfg('roles');
							unset($roles->member,$roles->anonymous);
							$options = [];
							foreach (array_keys((array)$roles) as $role) {
								if ($role=='admin' && !(in_array('admin',i()->roles) || i()->uid==1)) continue;
								$options[$role]=$role;
							}
							return $options;
						})(),
						'value'=>$this->userInfo->roles,
						'multiple'=>true,
						'description'=>'The user receives the combined permissions of the <em>authenticated user</em> role, and all roles selected here. For <a href="'.url('admin/user/access/uid/'.$rs->uid).'">additional authenticated for this user only</a>.',
					],
					'admin_remark' => [
						'type'=>'textarea',
						'label'=>'Admin Remark',
						'class'=>'-fill',
						'rows'=>2,
						'value'=>$this->userInfo->admin_remark
					],
					'submit' => [
						'type'=>'button',
						'items' => [
							'remove' => [
								'type'=>'text',
								'value'=>'<a class="sg-action btn -link" href="'.url('admin/user/delete/'.$this->userId).'" data-rel="#main" data-confirm="Delete this user, Are you sure?"><i class="icon -material">delete</i><span>Remove this user</span></a> Or ',
							],
							'cancel' => [
								'type'=>'cancel',
								'value'=>'<i class="icon -material">cancel</i><span>Cancel</span>'
							],
							'save' => [
								'type'=>'submit',
								'class'=>'-primary',
								'value'=>'<i class="icon -material">done_all</i><span>Save User Information</span>',
							],
						],
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Widget
		]);
	}
}
?>
<?php
// edit user information
function admin_user_edit($self,$uid) {
	$rs=R::Model('user.get',$uid);

	if ($rs->_empty) return message('error','User <em>'.$uid.'</em> not exists.');

	if ($_POST['cancel']) location('admin/user/list');

	$profile=(object)post('profile',_TRIM);

	if ($profile->username) {
		// //if (empty($profile->email)) $error[]='กรุณาป้อนอีเมล์';
		// if ($profile->email && !sg_is_email($profile->email)) $error[]='กรุณาป้อนอีเมล์ให้ถูกต้องตามรูปแบบ คือ yourname@domain.com';

		// if ($profile->password) {
		// 	if (strlen($profile->password)<6) $error[]='รหัสผ่านใหม่ต้องตัวอักษรอย่างน้อย 6 อักษร'; //-- password length
		// 	if ($profile->password != $profile->repassword) $error[]='การป้อนรหัสผ่านใหม่ทั้งสองครั้งไม่ตรงกัน'; //-- password <> retype
		// }

		// if ($error) {
		// 	$message=message('error',$error);
		// } else {
		// 	if ($profile->password) {
		// 		$profile->password=sg_encrypt($profile->password,cfg('encrypt_key'));
		// 		unset($profile->repassword);
		// 	} else {
		// 		unset($profile->password,$profile->repassword);
		// 	}
		// 	$profile->roles=implode(',',$profile->roles);
		// 	$oldRoles=mydb::select('SELECT `roles` FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$rs->uid)->roles;

		// 	// Delete cache when block or roles change
		// 	if ($profile->status=='block' || $profile->roles!=$oldRoles) {
		// 		mydb::query('DELETE FROM %cache% WHERE `headers`=:username',':username',$profile->username);
		// 	}

		// 	//unset($profile->username);

		// 	mydb::query(mydb::create_update_cmd('%users%',(array)$profile,' uid='.$rs->uid.' LIMIT 1'));

		// 	//location('admin/user/list');
		// 	return $ret;
		// }
	} else {
		$profile=(object)$rs;
	}

	$ret .= '<header class="header -box"><nav class="nav -back -hidden"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>Account information</h3></header>';

	$form = new Form([
		'variable' => 'profile',
		'action' => url('api/admin/user/'.$uid.'/edit'),
		'id' => 'edit-account',
		'class' => 'sg-form',
		'checkValid' => true,
		// 'rel' => 'none',
		'done' => 'close',
		'children' => [
			// if ($message) $form->addField('message',$message);
			'username' => [
				'type'=>'text',
				'label'=>'Username',
				'class'=>'-fill',
				'maxlength'=>30,
				'require'=>true,
				'value'=>htmlspecialchars($profile->username),
				'description'=>'Your preferred username; punctuation is not allowed except for periods, hyphens, and underscores.',
			],
			'email' => [
				'type'=>'text',
				'label'=>'E-mail address',
				'class'=>'-fill',
				'maxlength'=>50,
				'value'=>htmlspecialchars($profile->email),
				'description'=>'A valid e-mail address. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by e-mail.',
			],
			'password' => [
				'type'=>'password',
				'label'=>'Password',
				'maxlength'=>20,
				'class'=>'-fill',
			],
			'repassword' => [
				'type'=>'password',
				'label'=>'Confirm password',
				'class'=>'-fill',
				'maxlength'=>20,
				'description'=>'To change the current user password, enter the new password in both fields.',
			],
			'status' => [
				'type'=>'radio',
				'label'=>'Status :',
				'options' => [
					// if ($profile->status=='disable') $options['disable']='Disabled';
					'disable' => 'Disabled',
					'block' => 'Blocked',
					'enable' => 'Active',
					'locked' => 'Locked',
				],
				'value'=>$profile->status,
				'description'=>'To change the user status.',
			],
			'roles' => [
				'type'=>'checkbox',
				'label'=>'Roles :',
				'options' => (function() {
					$roles = cfg('roles');
					unset($roles->member,$roles->anonymous);
					$options = [];
					foreach (array_keys((array)$roles) as $role) {
						if ($role=='admin' && !(in_array('admin',i()->roles) || i()->uid==1)) continue;
						$options[$role]=$role;
					}
					return $options;
				})(),
				'value'=>$profile->roles,
				'multiple'=>true,
				'description'=>'The user receives the combined permissions of the <em>authenticated user</em> role, and all roles selected here. For <a href="'.url('admin/user/access/uid/'.$rs->uid).'">additional authenticated for this user only</a>.',
			],
			'admin_remark' => [
				'type'=>'textarea',
				'label'=>'Admin Remark',
				'class'=>'-fill',
				'rows'=>2,
				'value'=>htmlspecialchars($profile->admin_remark)
			],
			'submit' => [
				'type'=>'button',
				'items' => [
					'remove' => [
						'type'=>'text',
						'value'=>'<a class="sg-action btn -link" href="'.url('admin/user/delete/'.$uid).'" data-rel="#main" data-confirm="Delete this user, Are you sure?"><i class="icon -material">delete</i><span>Remove this user</span></a> Or ',
					],
					'cancel' => [
						'type'=>'cancel',
						'value'=>'<i class="icon -material">cancel</i><span>Cancel</span>'
					],
					'save' => [
						'type'=>'submit',
						'class'=>'-primary',
						'value'=>'<i class="icon -material">done_all</i><span>Save User Information</span>',
					],
				],
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();

	return $ret;
}
?>