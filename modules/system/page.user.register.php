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

function user_register($self) {
	if ($_POST['cancel']) location();

	//$self->theme->header->description = R::View('user.menu','register');

	$register = (Object) post('register',_TRIM+_STRIPTAG);

	// print_o($register,'$register',1);
	if (post('rel')) $register->rel = post('rel');

	if ($_POST) {
		$error = false;

		$register->username = strtolower($register->username);

		event_tricker('user.register_check',$self,$register,$form,$error);
		//$ret.='Format = '.cfg('member.username.format').' username='.$register->username;

		if (empty($register->username)) $error[]='กรุณาป้อน ชื่อสมาชิก (Username)';

		if (strlen($register->username)<4) $error[]='ชื่อสมาชิก (Username) อย่างน้อย 4 อักษร'; //-- username length

		if (!preg_match(cfg('member.username.format'),$register->username)) $error[]='ชื่อสมาชิก (Username) <strong><em>'.$register->username.'</em></strong> มีอักษรหรือความยาวไม่ตรงตามเงื่อนไข'; //-- check valid char

		if (mydb::count_rows('%users%', '`username` = "'.mydb::escape($register->username).'"'))
			$error[]='ชื่อสมาชิก (Username) <strong><em>'.$register->username.'</em></strong> มีผู้อื่นใช้ไปแล้ว'; //-- duplicate username

		if ($register->password=='') $error[]='กรุณาระบุ รหัสผ่าน (Password)'; //-- fill password

		if ($register->password && strlen($register->password)<6) $error[]='รหัสผ่าน (Password) ต้องยาวอย่างน้อย 6 อักษร'; //-- password length

		if ($register->password && $register->password != $register->repassword) $error[]='กรุณายืนยันรหัสผ่าน (Re-enter password) ให้เหมือนกันรหัสที่ป้อน'; //-- password <> retype

		if ($register->name=='') $error[]='กรุณาป้อน ชื่อสำหรับแสดง (Name)'; //-- fill name

		if ( mydb::count_rows('%users%','name="'.mydb::escape($register->name).'"') ) $error[]='ชื่อ <strong><em>'.$register->name.'</em></strong> มีผู้อื่นใช้ไปแล้ว'; //-- duplicate name

		if ( $register->email=='') $error[]='กรุณาป้อน อีเมล์ (E-mail)'; //-- fill email

		if ($register->email && !sg_is_email($register->email)) $error[]='อีเมล์ (E-mail) ไม่ถูกต้อง'; //-- invalid email

		if ($register->email && mydb::count_rows('%users%','email="'.mydb::escape($register->email).'"') ) $error[]='อีเมล์ <strong><em>'.$register->email.'</em></strong> ได้มีการลงทะเบียนไว้แล้ว หรือ <a href="'.url('user/password/get').'">ท่านจำรหัสผ่านไม่ได้</a>'; //-- duplicate email

		if ($register->step==2 && !sg_valid_daykey(5,$_POST['daykey'])) $error[]='Invalid Anti-spam word';
		// start saving new account

		if (!$error && $register->step==1) {
			return $ret.R::View('user.register.confirm',$register);
		} else if (!$error && $register->step==2) {

			// print_o($register,'$register',1);
			$result = UserModel::create($register);
			// print_o($result,'$result',1);
			// return $ret;

			switch (cfg('member.registration.method')) {
				case 'email' :
					$confirm_url=cfg('domain').url('user/email/confirm',array('code'=>$register->code));
					$mail->to=$register->email;
					//$mail->title='ยืนยันการสมัครเป็นสมาชิกเว็บไซท์ '.cfg('domain.short');
					$mail->title=cfg('domain.short').' : '.tr('Confirm to be member of').' '.cfg('domain.short');
					$mail->from=cfg('member.registration.email');
					$mail->encoding='UTF-8';
					$mail->body='<p>สวัสดีครับ</p>
<p>ท่านได้ทำการลงทะเบียนเพื่อสมัครเป็นสมาชิกของเว็บไซท์ '.cfg('domain.short').'</p>

<p>ชื่อ ( username ) : '.$register->username.'<br />
รหัสผ่าน ( password ) : '.$register->repassword.'<br />
อีเมล์ ( e-mail ) : '.$register->email.'</p>

<p>ขอให้ท่านยืนยันการสมัครเป็นสมาชิก โดย <a href="'.$confirm_url.'">คลิกยืนยันการเป็นสมาชิก</a><br />หรือนำที่อยู่นี้ -> '.$confirm_url.' ไปวางไว้ในช่องที่อยู่ของเบราเซอร์ที่ท่านใช้<p>

<p>ขอแสดงความนับถือ</p>

<p>Web Team.</p>';
					$mail->body.='<p>Hi</p>
<p>You are register on website '.cfg('domain.short').'</p>
<p>username : '.$register->username.'<br />
password : '.$register->repassword.'<br />
e-mail : '.$register->email.'</p>
<p>Plese confirm by <a href="'.$confirm_url.'">click here to confirm</a><br />or copy url address -> '.$confirm_url.' and paste in address bar of your browser</p>

<p>Sincerely,</p>

<p>Web Team.</p>';

					BasicModel::sendmail($mail);
					//BasicModel::sendmail($mail,'PHPMailer');
					$ret .= message('status','Member register complete');
					$ret .= '<p>ได้ทำการส่งอี-เมล์ไปที่ <strong>'.$register->email.'</strong> เรียบร้อย ให้ท่านเปิดอ่านอีเมล์และคลิกยืนยันการเป็นสมาชิก การสมัครสมาชิกจึงจะสมบูรณ์</p><p>หากท่านไม่ได้รับอีเมล์ กรุณาตรวจสอบในพื้นที่ของ <strong>Junk mail</strong> ด้วย</p>';
					return $ret;
					break;

				case 'waiting' :
					$ret.=message('status',tr('Member register complete'));
					$ret.='<p>กรุณารอสักครู่ เพื่อให้ผู้ดูแลระบบตรวจสอบและกำหนดสิทธิ์ในการใช้งานให้ท่าน</p>';
					R()->user = UserModel::signInProcess($register->username,$register->repassword);
					return $ret;

				case 'waiting,email' :
					$ret.=message('status',tr('Member register complete'));
					$ret.='<p>กรุณารอสักครู่ เพื่อให้ผู้ดูแลระบบตรวจสอบและกำหนดสิทธิ์ในการใช้งานให้ท่าน และจะส่งอี-เมล์ยืนยันการใช้งานไปที่ <strong>'.$register->email.'</strong> ให้ท่านเปิดอ่านอีเมล์และคลิกยืนยันการเป็นสมาชิก การสมัครสมาชิกจึงจะสมบูรณ์</p><p>หากท่านไม่ได้รับอีเมล์ กรุณาตรวจสอบในพื้นที่ของ <strong>Junk mail</strong> ด้วย';
					R()->user = UserModel::signInProcess($register->username,$register->repassword);
					return $ret;

				default :
					$ret .= message('status','Member register complete');
					unset($GLOBALS['counter']->members);
					CounterModel::make(cfg('counter'));
					cfg_db('counter',$GLOBALS['counter']);
					break;
			}
			R()->user = UserModel::signInProcess($register->username,$register->repassword);
			$retLoc = SG\getFirst($register->ret,'profile/'.$register->uid);
			location($retLoc);
			return $ret;
		}
	}

	$ret .= message('error',$error);

	if (post('signWith') === 'google') {
		$jwt = Jwt::isValid(post('credential'));

		if ($jwt->payload->email) {
			return new Form([
				'title' => 'Sign Up With Google',
				'action' => url('api/user/register/google'),
				'class' => 'sg-form',
				'checkValid' => true,
				'rel' => 'notify',
				'done' => 'reload:'.url('api/system/date', ['credential' => post('credential')]),
				'children' => [
					new Container([
						'class' => '-sg-text-center',
						'child' => '<img class="profile-photo" src="'.$jwt->payload->picture.'" /> '.$jwt->payload->email,
					]), // Container
					'name' => [
						'type' => 'text',
						'class' => '-fill',
						'require' => true,
						'value' => $jwt->payload->name,
						'placeholder' => 'ระบุชื่อ-นามสกุล',
					],
					'email' => [
						'type' => 'text',
						'class' => '-fill',
						'readonly' => true,
						'value' => $jwt->payload->email,
					],
					'googleToken' => [
						'type' => 'hidden',
						'value' => post('credential'),
					],
					'accept' => [
						'type' => 'checkbox',
						'require' => true,
						'options' => ['yes' => 'ใช่, ฉันเข้าใจและยอมรับข้อตกลงรวมทั้งเงื่อนไขในการใช้บริการ'],
					],
					'save' => [
						'type' => 'button',
						'value' => 'Create my account',
						'class' => '-primary -fill',
					],
					new Container(['class' => '-sg-text-center', 'child' => 'ฉันมีบัญชีการใช้งานอยู่แล้ว? <a class="btn -link" href="'.url('my').'"><i class="icon -material">login</i><s[an>เข้าสู่ระบบสมาชิก</span></a>']), // Container
				], // children
			]);
		}
	} else if ($error && $register->step == 1) {
		$ret .= R::View('user.register.form',$register);
	} else if ($error && $register->step == 2) {
		$ret .= R::View('user.register.confirm',$register);
	} else {
		if (post('ret')) $register->ret = post('ret');
		$ret .= R::View('user.register.form',$register);
	}
	return $ret;
}
?>