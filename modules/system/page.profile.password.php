<?php
function profile_password($self, $uid = NULL) {
	if (empty($uid)) $uid=i()->uid;

	$user = R::Model('user.get',$uid);

	if (!user_access('administer users','change own profile',$user->uid)) return message('error','Access denied');

	if ($_POST['cancel']) location('profile/'.$user->uid);

	R::View('profile.toolbar',$self,$user->uid);

	$ret='<h3>{tr:Change password}</h3>';

	$password=(object)post('profile',_TRIM);

	if ($password->current) {
		if ($password->current === '' ) $error[]='กรุณาระบุรหัสผ่านปัจจุบัน'; //-- fill password
		if (!($password->current===sg_decrypt($user->password,cfg('encrypt_key')))) $error[]='รหัสผ่านปัจจุบันไม่ถูกต้อง';
		if (strlen($password->password)<6) $error[]='รหัสผ่านใหม่ต้องตัวอักษรอย่างน้อย 6 อักษร'; //-- password length
		if (strlen($password->password)!=strlen($password->repassword) || $password->password!=$password->repassword) $error[]='การป้อนรหัสผ่านใหม่ทั้งสองครั้งไม่ตรงกัน'; //-- password <> retype
		if (!$error) {
			$newpassword=sg_encrypt($password->password,cfg('encrypt_key'));
			mydb::query('UPDATE %users% SET password = :password WHERE uid = :uid LIMIT 1', ':password', $newpassword, ':uid' , $user->uid);
			$ret .= message('status','New password was change : บันทึกรหัสผ่านใหม่เรียบร้อย');
			location('my');
			return $ret;
		}
	}

	$ret.='<div class="help">เปลี่ยนรหัสผ่านของคุณบ้าง เป็นการป้องกันไว้ก่อน</div>';
	if ($error) $ret.=message('error',$error);

	$form = new Form('profile', url(q()),NULL,'edit-topic');

	$form->addField(
		'current',
		array(
			'type' => 'password',
			'label' => 'รหัสผ่านปัจจุบัน',
			'maxlength' => cfg('member.password.maxlength'),
			'class' => '-fill',
			'require' => true,
			'description' => 'ป้อนรหัสผ่านที่ใช้งานอยู่ในปัจจุบัน',
		)
	);

	$form->addField(
		'password',
		array(
			'type' => 'password',
			'label' => 'รหัสผ่านใหม่',
			'maxlength' => cfg('member.password.maxlength'),
			'class' => '-fill',
			'require' => true,
			'description' => 'ป้อนรหัสผ่านใหม่ที่ต้องการเปลี่ยน',
		)
	);

	$form->addField(
		'repassword',
		array(
			'type' => 'password',
			'label' => 'รหัสผ่านใหม่ (ยืนยัน)',
			'maxlength' => cfg('member.password.maxlength'),
			'class' => '-fill',
			'require' => true,
			'description' => 'ป้อนรหัสผ่านใหม่อีกครั้งเพื่อยืนยันความถูกต้อง',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="btn -link -cancel" href=""><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$form->addText('<div class="help">เพื่อความรวดเร็ว ในการเปลี่ยน รหัสผ่าน กรุณาป้อนรหัสผ่านปัจจุบัน , รหัสผ่านใหม่ และ ยืนยันรหัสผ่านใหม่ ให้ถูกต้อง</div>');

	$ret .= $form->build();

	return $ret;
}
?>