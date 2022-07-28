<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function my_change_password($self) {
	if (!R()->appAgent) $self->theme->sidebar = R::View('my.menu')->build();

	R::View('toolbar', $self, 'Change Password @'.i()->name);

	$post = (object) post('profile',_TRIM);

	if ($post->current && $post->password && $post->repassword) {
		$userInfo = R::Model('user.get', i()->uid);

		if ($post->current === '' ) $error[]='กรุณาระบุรหัสผ่านปัจจุบัน'; //-- fill password
		if (!($post->current === sg_decrypt($userInfo->password,cfg('encrypt_key')))) $error[]='รหัสผ่านปัจจุบันไม่ถูกต้อง';
		if (strlen($post->password) < 6) $error[] = 'รหัสผ่านใหม่ต้องตัวอักษรอย่างน้อย 6 อักษร'; //-- password length
		if (strlen($post->password) != strlen($post->repassword) || $post->password != $post->repassword) $error[]='การป้อนรหัสผ่านใหม่ทั้งสองครั้งไม่ตรงกัน'; //-- password <> retype

		if ($error) {
			header('HTTP/1.0 406 Not Acceptable');
			$ret .= implode(',', $error);
			return $ret;
		} else {
			$newpassword = sg_encrypt($post->password,cfg('encrypt_key'));
			mydb::query('UPDATE %users% SET password = :password WHERE uid = :uid LIMIT 1', ':password', $newpassword, ':uid' , $userInfo->uid);

			$ret .= 'New password was change : บันทึกรหัสผ่านใหม่เรียบร้อย';
		}
		return $ret;
	}


	$ret='<header class="header -box">'._HEADER_BACK.'<h3>{tr:Change password}</h3></header>';

	$ret .= '<div class="help">เปลี่ยนรหัสผ่านของคุณบ้าง เป็นการป้องกันไว้ก่อน</div>';
	if ($error) $ret.=message('error',$error);

	$form = new Form('profile', url('my/change/password'),'edit-topic','sg-form');
	$form->addData('checkValid', true);

	$form->addData('rel','notify');
	$form->addData('done', 'close | reload:'.url('my'));

	$form->addField(
		'current',
		array(
			'type' =>'password',
			'label' => 'รหัสผ่านปัจจุบัน',
			'maxlength' => cfg('member.password.maxlength'),
			'class' => '-fill',
			'require' => true,
			'placeholder' => 'Enter current password',
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
			'placeholder' => 'Enter new password',
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
			'placeholder' => 'Re Enter new password',
			'description' => 'ป้อนรหัสผ่านใหม่อีกครั้งเพื่อยืนยันความถูกต้อง',
		)
	);

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('my',array('closewebview'=>'YES')).'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$form->addText('<div class="help">เพื่อความรวดเร็ว ในการเปลี่ยน รหัสผ่าน กรุณาป้อนรหัสผ่านปัจจุบัน , รหัสผ่านใหม่ และ ยืนยันรหัสผ่านใหม่ ให้ถูกต้อง</div>');

	$ret .= $form->build();


	return $ret;
}
?>