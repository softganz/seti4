<?php
/**
* Admin   :: Edit User Information
* Created :: 2023-03-31
* Modify  :: 2025-01-16
* Version :: 3
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

	function rightToBuild() {
		if (empty($this->userId)) return error(_HTTP_ERROR_BAD_REQUEST,'User <em>'.$this->userId.'</em> not exists.');
		if ($this->userId === 1) return error(_HTTP_ERROR_FORBIDDEN, 'Access denied');
		return true;
	}

	function build() {

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
						'type' => 'text',
						'label' => 'Username',
						'class' => '-fill',
						'maxlength' => 30,
						'require' => true,
						'value' => $this->userInfo->username,
						'description' => 'Your preferred username; punctuation is not allowed except for periods, hyphens, and underscores.',
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
						'type'=>'text',
						'label'=>'Password',
						'maxlength'=>20,
						'class'=>'-fill',
					],
					'repassword' => [
						'type'=>'text',
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
						'description'=>'The user receives the combined permissions of the <em>authenticated user</em> role, and all roles selected here. For <a href="'.url('admin/user/access/uid/'.$this->userId).'">additional authenticated for this user only</a>.',
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