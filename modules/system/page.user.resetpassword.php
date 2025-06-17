<?php
/**
* User    :: Reset Password
* Modify  :: 2025-06-16
* Version :: 2
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function user_resetpassword($self) {
	$token=post('sgpwcd');
	$rs=mydb::select('SELECT * FROM %users% WHERE `code`=:token LIMIT 1',':token',$token);
	$tokenexpire=date('U')-60*60; // expire in 60 min
	if ($rs->_empty) {
		$ret.=message('error','Password token error.');
	} else if ($rs->pwresettime<$tokenexpire) {
		$ret.=message('error','Password request time expired.');
	} else {
		if (post('save')) {
			$post=(object)post('resetpassword');
			if ($post->password=='') $error[]='กรุณาระบุ รหัสผ่าน (Password)'; //-- fill password
			if ($post->password && strlen($post->password)<6) $error[]='รหัสผ่าน (Password) ต้องยาวอย่างน้อย 6 อักษร'; //-- password length
			if ($post->password && $post->password != $post->repassword) $error[]='กรุณายืนยันรหัสผ่าน (Re-enter password) ให้เหมือนกันรหัสที่ป้อน'; //-- password <> retype
			if (!$error) {
				$password=sg_encrypt($post->password,cfg('encrypt_key'));
				$stmt='UPDATE %users% SET `password`=:password, `code`=NULL, `pwresettime`=NULL WHERE `uid`=:uid LIMIT 1';
				mydb::query($stmt,':uid',$rs->uid, ':password',$password);
				$ret.=message('notify','บันทึกรหัสผ่านใหม่เรียบร้อย กรุณาเข้าสู่ระบบสมาชิกอีกครั้งด้วยรหัสใหม่');
				$ret.='<p><a class="btn" href="'.url('user').'">คลิกที่นี่ เพื่อเข้าสู่ระบบสมาชิกด้วยรหัสใหม่</a></p>';
				LogModel::save([
					'module' => 'user',
					'keyword' => 'Password request confirm',
					'message' => 'Password request of '.$rs->username.' was changed.'
				]);
				return $ret;
			}
		}

		LogModel::save([
			'module' => 'user',
			'keyword' => 'Password request click',
			'message' => 'Password request of '.$rs->username.' was click.'
		]);
		$ret.='<h2>Enter new password</h2>';
		$form=new Form('resetpassword',url(q()),'user-resetpassword','sg-form');
		$form->addData('checkValid',true);

		$form->addField('sgpwcd',array('type'=>'hidden','name'=>'sgpwcd','value'=>$token));

		$form->addField(
			'password',
			array(
				'type'=>'password',
				'label'=>'Enter new password',
				'maxlength'=>20,
				'class'=>'-fill',
				'require'=>true
				)
			);
		$form->addField(
			'repassword',
			array(
				'type'=>'password',
				'label'=>'RE-Enter new password',
				'maxlength'=>20,
				'class'=>'-fill',
				'require'=>true
				)
			);

		$form->addField('send',array('type'=>'button','name'=>'save','value'=>'Save new password'));
		$ret .= $form->build();
		if ($error) $ret.=message('error',$error);
	}
	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($rs,'$rs');
	return $ret;
}
?>