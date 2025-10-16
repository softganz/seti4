<?php
/**
 * Create many users in one click
 *
 * @param $_POST
 * @return String
 */
function admin_user_create($self) {
	$ret .='<h2>Create many users</h2>'._NL;
	if ($_POST['cancel']) location('admin/user');


	$post=(object)post();

	if (post('cancel')) location('admin/user');
	if ($post->users) {
		$lines=explode("\n",$post->users);
		foreach ($lines as $key => $line) {
			$data = list($uid,$username,$password,$name,$email,$roles,$phone,$address,$admin_remark) = explode(',',$line);
			if (empty($uid)) $uid = NULL;
			$username = trim($username);
			$password = trim($password);
			if (empty($password)) $password = substr(md5(uniqid()), 0, 8);
			$name = trim($name);
			$email = trim($email);
			$roles = str_replace('+',',',trim($roles));
			$phone = trim($phone);
			$epassword = sg_encrypt($password,cfg('encrypt_key'));
			$datein = date('Y-m-d H:i:s');
			$status = 'enable';
			$admin_remark = trim($admin_remark);
			$data[2] = $password;
			if (empty($username) || empty($password)) continue;
			if (empty($name)) $name = $username;
			$stmt = 'INSERT INTO %users%
				(`uid`, `username`, `password`, `name`, `status`, `roles`, `email`, `phone`, `address`, `admin_remark`, `datein`)
				VALUES
				(:uid, :username, :password, :name, :status, :roles, :email, :phone, :address, :admin_remark, :datein)';

			mydb::query($stmt,':uid',$uid, ':username',$username, ':password',$epassword, ':name',$name, ':status',$status, ':roles',$roles, ':email',$email, ':phone',$phone, ':address',$address, ':admin_remark',$admin_remark, ':datein',$datein);

			if (!mydb()->_error) {
				$data[0] = mydb()->insert_id;
				$complete[] = implode(',', $data);
				unset($lines[$key]);
			} else {
				$ret .= '<p>'.mydb()->_query.'</p>';
			}
		}
		$post->users = implode("\n",$lines);
	}

	$form = new Form('profile',url(q()),'admin-user-create');

	$form->addField(
		'users',
		array(
			'type'=>'textarea',
			'label'=>'User description :<br />uid,username,password,name,email,roles,phone,address',
			'name'=>'users',
			'class'=>'-fill',
			'rows'=>20,
			'cols'=>60,
			'value'=>htmlspecialchars($post->users),
			'description'=>'Enter username,password,name,email,roles,phone. Each user per line'
		)
	);

	$form->addField(
		'submit',
		array(
			'type'=>'button',
			'items'=>array(
				'save'=>array(
					'type'=>'submit',
					'class'=>'-primary',
					'value'=>'<i class="icon -material">done_all</i><span>Create users</span>'
				),
				'cancel'=>array(
					'type'=>'cancel',
					'value'=>'<i class="icon -material">cancel</i><span>Cancel</span>'
				),
				'reset'=>array(
					'type'=>'reset',
					'value'=>'<i class="icon -material">restart_alt</i><span>Reset</span>'
				),
			),
		)
	);

	$ret.=$form->build();

	if ($complete) {
		$ret.='<h3>User was created</h3>';
		$ret.=implode('<br />',$complete);
	}
	return $ret;
}
?>