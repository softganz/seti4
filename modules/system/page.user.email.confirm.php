<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

import('model:user.php');

function user_email_confirm($self) {
	$para=para(func_get_args());

	$confirmCode = post('code');

	$self->theme->title='User registration confirm';

	user_menu('home','home',url());
	BasicModel::member_menu();
	$self->theme->navigator=user_menu();

	if (empty($confirmCode)) $error[]='Invalid register confirm code';

	if (!$error) {
		$result = UserModel::emailConfirm('user.email.confirm',$confirmCode);

		if ($result->complete) {
			$ret.=message('status','User registration confirm complete');
			$ret.='<p>การยืนยันการสมัครเป็นสมาชิกของเว็บไซท์ '.cfg('domain.short').' เสร็จสมบูรณ์ กรุณา Sign in ด้วย username และ password ที่ท่านได้รับจากอีเมล์ เพื่อเข้าสู่ระบบสมาชิกต่อไป</p>';
			$ret .= R::View('signform','{action:"'.url('profile/'.$result->user->uid).'"}');
		} else {
			$error[]='Registration confirm error : <ul><li>'.implode('</li><li>',$result->error).'</li></ul>';
		}
	}
	if ($error) $ret.=message('error',$error);

	return $ret;
}
?>