<?php
/**
* User    :: New User Register
* Created :: 2024-02-14
* Modify  :: 2024-05-11
* Version :: 4
*
* @return Widget
*
* @usage user/register
*/

class UserRegister extends Page {
	var $confirm;
	// var $confirmRegister;
	var $signWith;
	var $rel;
	var $ret;
	var $register;

	function __construct() {
		parent::__construct([
			'confirm' => post('confirm'),
			// 'confirmRegister' => post('confirmRegister'),
			'signWith' => post('signWith'),
			'rel' => post('rel'),
			'ret' => post('ret'),
			'register' => (Object) post('register',_TRIM+_STRIPTAG),
		]);
		$this->register->rel = SG\getFirst($this->register->rel, $this->rel);
		$this->register->ret = SG\getFirst($this->register->ret, $this->ret);
		$this->register->username = strtolower($this->register->username);
		$this->register->email = strtolower($this->register->email);
	}

	function rightToBuild() {
		return true;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => tr('Register New Member'),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					(function(){
						if ($this->signWith === 'google') return $this->signWithGoogle();
						else return new UserRegisterFormWidget($this->register);
					})()
				], // children
			]), // Widget
		]);
	}

	function user_register() {
		if ($_POST['cancel']) location();

		//$self->theme->header->description = R::View('user.menu','register');

		$register = (Object) post('register',_TRIM+_STRIPTAG);

		// print_o($register,'$register',1);
		if (post('rel')) $register->rel = post('rel');

 		if ($error && $register->step == 1) {
			$ret .= R::View('user.register.form',$register);
		} else if ($error && $register->step == 2) {
			$ret .= R::View('user.register.confirm',$register);
		} else {
			if (post('ret')) $register->ret = post('ret');
			$ret .= R::View('user.register.form',$register);
		}
		return $ret;
	}

	function signWithGoogle() {
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
		} else {
			return error(_HTTP_ERROR_BAD_REQUEST, 'ไม่มีข้อมูลจาก Google');
		}
	}

	function verify() {
		return Poison::getDayKey(5,true);
	}

	function save() {
		$cfgUserRegister = cfg('user')->register;


		// Check referer domain must same as current domain
		$fromDomain = parse_url($_SERVER['HTTP_REFERER']);
		if ($fromDomain['host'] != _DOMAIN_SHORT) {
			http_response_code(_HTTP_ERROR_NOT_ACCEPTABLE);
			return ['submit' => ['Invalid source domain']];
		}

		if ($errors = $this->checkValid()) {
			http_response_code(_HTTP_ERROR_NOT_ACCEPTABLE);
			return $errors;
		}

		// if ($cfgUserRegister->confirmForm && !$this->confirmRegister) {
		// 	return $this->confirmForm();
		// }

		$result = $this->createUser();

		return [
			'location' => SG\getFirst($this->register->ret,'my'),
			'register' => $this->register,
		];
	}

	private function checkValid() {
		$cfgUserRegister = cfg('user')->register;
		$validCheck = explode(',', $cfgUserRegister->valid);

		$errors = [];

		if (empty($this->register->username)) $errors['username'][] = 'กรุณาป้อน ชื่อสมาชิก (Username)';

		if ($this->register->username) {
			if (strlen($this->register->username) < 4) $errors['username'][] = 'ชื่อสมาชิก (Username) อย่างน้อย 4 อักษร'; //-- username length

			if (!preg_match($cfgUserRegister->usernameMatch,$this->register->username)) $errors['username'][] = 'ชื่อสมาชิก (Username) <strong><em>'.$this->register->username.'</em></strong> มีอักษรหรือความยาวไม่ตรงตามเงื่อนไข'; //-- check valid char

			if (mydb::count_rows('%users%', '`username` = "'.mydb::escape($this->register->username).'"'))
				$errors['username'][] = 'ชื่อสมาชิก (Username) <strong><em>'.$this->register->username.'</em></strong> มีผู้อื่นใช้ไปแล้ว'; //-- duplicate username
		}

		// Check password valid
		if (!UserValidModel::checkPasswordValid($this->register->password, $passwordError)) {
			$errors['password'] = $passwordError;
		}

		// Check re-password valid
		if (!UserValidModel::checkRePasswordValid($this->register->password, $this->register->repassword, $rePasswordError)) {
			$errors['repassword'] = $rePasswordError;
		}

		if ($this->register->name == '') $errors['name'][] = 'กรุณาป้อน ชื่อสำหรับแสดง (Name)'; //-- fill name

		if ( mydb::count_rows('%users%','name="'.mydb::escape($this->register->name).'"') ) $errors['name'][] = 'ชื่อ <strong><em>'.$this->register->name.'</em></strong> มีผู้อื่นใช้ไปแล้ว'; //-- duplicate name

		if ( $this->register->email == '') $errors['email'][] = 'กรุณาป้อน อีเมล์ (E-mail)'; //-- fill email

		if ($this->register->email && !sg_is_email($this->register->email)) $errors['email'][] = 'อีเมล์ (E-mail) ไม่ถูกต้อง'; //-- invalid email

		if ($this->register->email && mydb::count_rows('%users%','email="'.mydb::escape($this->register->email).'"') ) $errors['email'][] = 'อีเมล์ <strong><em>'.$this->register->email.'</em></strong> ได้มีการลงทะเบียนไว้แล้ว หรือ <a href="'.url('user/password').'">ท่านจำรหัสผ่านไม่ได้</a>'; //-- duplicate email

		// preg_match('/rightbliss/i', 'hrightblissbeauty', $out);
		// print_r($out);

		if (sg::is_spam_word([$this->register->email])) $errors['accept'][] = 'มีคำต้องห้ามอยู่ในข้อมูลที่ป้อนเข้ามา';

		if (!$this->register->accept) $errors['accept'][] = 'กรุณายอมรับข้อตกลงการใช้งาน';

		return $errors;
	}

	private function confirmForm() {
		return ['Confirm?'];
	}

	private function createUser() {
		$result = UserModel::create($this->register);
		// print_o($result,'$result',1);
		// return $ret;

		switch (cfg('user')->registrationConfirmMethod) {
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
		R()->user = UserModel::signInProcess($this->register->username,$this->register->password);

		return $result;
	}

}
?>