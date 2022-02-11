<?php
/**
* Creater member
*
* @param Object $self
* @return String
*/
function flood_admin_createuser($self) {
	$self->theme->title='Create New User';
	$self->theme->sidebar=R::Page('flood.admin.menu','member');
	$q=post('q');
	$uid=post('id');
	$order=SG\getFirst($para->order,post('o'),'CONVERT(u.`name` USING tis620)');
	$sort=SG\getFirst($para->sort,post('s'),'ASC');

	$post=(object)post('user');
	if ($post->username) {
		if (db_count('%users%','username="'.$post->username.'"') ) $error[]='ชื่อสมาชิก (Username) <strong><em>'.$post->username.'</em></strong> มีผู้อื่นใช้ไปแล้ว'; //-- duplicate username
		if (empty($post->password) || empty($post->name)) $error[]='กรุณาป้อนข้อมูลให้ครบถ้วน';
		if (!$error) {
			$post->username=strtolower(trim($post->username));
			$post->password=trim($post->password);
			if (empty($post->password)) $post->password=substr(md5(uniqid()), 0, 8);
			$post->name=trim($post->name);
			$post->email=trim($post->email);
			$post->roles=str_replace('+',',',trim($post->roles));
			$post->phone=trim($post->phone);
			$post->epassword=sg_encrypt($post->password,cfg('encrypt_key'));
			$post->datein=date('Y-m-d H:i:s');
			$post->status='enable';
			if (empty($post->name)) $post->name=$post->username;
			$stmt='INSERT INTO %users%
						(`username`, `password`, `name`, `status`, `roles`, `email`, `phone`, `datein`)
						VALUES
						(:username, :epassword, :name, :status, :roles, :email, :phone, :datein)';
			mydb::query($stmt,$post);
			if (!mydb()->_error) {
				$uid=mydb()->insert_id;
				// Add member to organization
				if ($post->orgid) {
					mydb::query('INSERT INTO %org_officer% (`orgid`,`uid`,`membership`) VALUES (:orgid,:uid,"officer")',':orgid',$post->orgid, ':uid',$uid);
				}
			}
			location('flood/admin/member');
		}
	}

	if ($error) $ret.=message('error',$error);
	$ret.=__project_admin_user_form($post);

	//$ret.=print_o(post(),'post');
	return $ret;
}

function __project_admin_user_form($post) {
	$form = new Form([
		'variable' => 'user',
		'action' => url('flood/admin/createuser'),
		'id' => 'org-add-user',
		'children' => [
			'orgid' => ['type' => 'hidden', 'value' => $post->orgid],
			'<h3>รายละเอียดสมาชิก</h3>',
			'username' => [
				'type' => 'text',
				'label' => 'ชื่อสมาชิก (Username)',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($post->username),
			],
			'password' => [
				'type' => 'text',
				'label' => 'รหัสผ่าน (Password)',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($post->password),
			],
			'name' => [
				'type' => 'text',
				'label' => 'ชื่อ-นามสกุลจริง (Real Name)',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($post->name),
			],
			'email' => [
				'type' => 'text',
				'label' => 'อีเมล์ (E-Mail)',
				'class' => '-fill',
				'value' => htmlspecialchars($post->email),
			],
			'phone' => [
				'type' => 'text',
				'label' => 'โทรศัพท์ (Phone)',
				'class' => '-fill',
				'value' => htmlspecialchars($post->phone),
			],
			'roles' => [
				'type' => 'radio',
				'label' => 'กลุ่มสมาชิก',
				'value' => $post->roles,
				'options' => (function() {
					$roles = [];
					foreach (cfg_db('roles') as $key => $value) {
						if (in_array($key,array('anonymous','member','admin'))) continue;
						$roles[$key] = $key;
					}
					return $roles;
				})(),
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	// foreach (cfg_db('roles') as $key => $value) {
	// 	if (in_array($key,array('anonymous','member','admin'))) continue;
	// 	$form->roles->options[$key]=$key;
	// }

	return $form->build();
}
?>