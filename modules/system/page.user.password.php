<?php
/**
* User    :: Reset Passsword Request Form
* Created :: 2021-11-26
* Modify  :: 2025-06-2ถ
* Version :: ุ
*
* @return Widget
*
* @usage user/password
*/

use Softganz\DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

import('package:external/PHPMailer/src/PHPMailer.php');
import('package:external/PHPMailer/src/SMTP.php');
import('package:external/PHPMailer/src/Exception.php');

class UserPassword extends Page {
	var $host = 'mail.namfaa.com';

	function build() {
		$post = (Object) post('request');

		if ($post->username) $result = $this->sendMail($post->username);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Forgot Password!!!',
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'variable' => 'request',
						'action' => url(q()),
						'id' => 'password-request',
						'class' => 'sg-form',
						'checkValid' => true,
						'children' => [
							'username' => [
								'type' => 'text',
								'label' => 'โปรดระบุ username หรือ email address ของท่าน',
								'maxlength' => 50,
								'class' => '-fill',
								'require' => true,
								'value' => $post->username,
								'placeholder' => 'ระบุ username หรือ email',
							],
							'send' => [
								'type' => 'button',
								'value' => '<i class="icon -material">email</i><span>Send reset password</span>',
								'container' => '{class: "-sg-text-right"}',
							],
							$result ? '<div class="notify">'.$result.'</div>' : NULL,
							'ให้ท่านป้อน username หรือ email address ที่ท่านลงทะเบียนไว้กับเว็บไซท์ แล้วระบบจะส่งรหัสผ่านไปให้ท่านยัง email ที่ท่านได้ทำการลงทะเบียนไว้ (อีเมล์อาจจะถูกย้ายไปอยู่ใน Spam mail - เมล์ขยะ กรุณาตรวจสอบด้วย)',
						], // children
					]), // Form
				], // children
			]), // Widget
		]);
	}

	// Generate uniq token
	private static function token() {
		do {
			$token = md5(uniqid($username.rand(), true));
			$tokenrs = DB::select([
				'SELECT `code` FROM %users% WHERE `code` = :token LIMIT 1',
				'var' => [':token' => $token]
			]);
		} while ($tokenrs->code);

		return $token;
	}

	// Generate mail message
	private static function mailMessage($username, $name, $email, $token) {
		$resetUrl = _DOMAIN.url('user/resetpassword', ['sgpwcd' => $token]);

		$message = 'สวัสดี คุณ '.$name
			. '<p>คุณได้ร้องขอการยกเลิกรหัสผ่านของเว็บไซต์ '._DOMAIN.' โดยมีรายละเอียดผู้ใช้งานดังนี้<p>'
			. '<p>user name : '.$username.'<br />'
			. 'email address : '.$email.'</p>'
			. '<p>กรุณา <a href="'.$resetUrl.'" target="_blank">คลิกลิงนี้</a> หรือ สำเนาที่อยู่ด้านล่างแล้วนำไปวางในช่อง address bar ของ browser และดำเนินการตามขั้นตอนด่อไป ลิงก์นี้จะสามารถใช้งานได้ภายใน 60 นาที<br><br>'.$resetUrl.'<br><br>กรุณาไม่ตอบกลับอีเมล์ฉบับนี้</p>'
			. '<hr>'
			. '<p>Password request from <strong>'._DOMAIN.'</strong> with detail below</p>'
			. '<p>user name : '.$username.'<br />'
			. 'email address : '.$email.'</p>'
			. '<p>Please <a href="'.$resetUrl.'">click this link</a> or copy url below and paste into browser address bar</p> and following process. This link will expire within 60 minutes.<br /><br />'.$resetUrl.'</p>'
			. '<p>Please do not reply this mail</p>';

		return $message;
	}

	// Send email
	private function sendMail($username) {
		if (!load_lib('class.mail.php', 'lib')) return 'Mail module error';
		$mail = new Mail();
		if ($mail->IsValidEmail($username)) { // input email
			$get_by_email = $username;
		} else { // input username
			$get_by_email = 'ตามที่ท่านได้ลงทะเบียนไว้ในชื่อ '.$username;
		}

		$users = DB::select([
			'SELECT `uid`, `username`, `name`, `email` FROM %users% %WHERE%',
			'where' => [
				'%WHERE%' => [
					$mail->IsValidEmail($username) ? ['`email` = :email', ':email' => $username] : ['`username` = :username', ':username' => $username],
					['`status` = "enable"'],
				]
			]
		]);

		if (empty($users->items)) return 'ไม่มีข้อมูลสมาชิกตามรายละเอียดที่ท่านระบุ';

		// debugMsg($users, '$users');

		$cfgMail = cfg('mail');

		list(,$mailDomain) = explode('@', $cfgMail->username);

		foreach ($users->items as $user) {
			$token = self::token();
			$mail = new PHPMailer(true);
			$logMsg = $username.' request new password.';

			try {
				//Server settings
				$mail->CharSet = 'UTF-8';
				$mail->isSMTP();
				$mail->Host       = $cfgMail->host; // Set your SMTP server
				$mail->SMTPAuth   = true;
				$mail->Username   = $cfgMail->username; // SMTP username
				$mail->Password   = $cfgMail->password; // SMTP password
				$mail->SMTPSecure = $cfgMail->SMTPSecure;
				$mail->Port       = $cfgMail->port;

				//Recipients
				$mail->setFrom('noreply@'.$mailDomain, 'noreply@'._DOMAIN_SHORT);
				$mail->addAddress($user->email, $user->name);

				// Content
				$mail->isHTML(true);
				$mail->Subject = 'ท่านร้องขอรหัสผ่านจาก '._DOMAIN_SHORT.' : Password request from '._DOMAIN_SHORT;
				$mail->Body    = self::mailMessage($user->username, $user->name, $user->email, $token);
				$mail->AltBody = 'This is the plain text body';

				$mail->send();

				$message= 'ได้ส่งข้อมูลไปให้ท่านที่อีเมล์ <strong>'.$get_by_email.'</strong> เรียบร้อยแล้ว :<br />กรุณาตรวจสอบอีเมล์ตามที่ท่านระบุไว้ในทะเบียนสมาชิก และคลิกลิงก์เพื่อเปลี่ยนรหัสผ่าน<br />หากยังไม่ได้รับอีเมล์ ให้ลองตรวจสอบใน <b>เมล์ขยะ (Spam mail)</b> ด้วยเพราะอาจจะถูกนำไปเก็บไว้ในนั้น';
				$logMsg .= ' And password is send to email complete.';

				DB::query([
					'UPDATE %users% SET `code` = :token, `pwresettime` = :now WHERE `uid` = :uid LIMIT 1',
					'var' => [
						':token' => $token,
						':now' => date('U'),
						':uid' => $user->uid
					]
				]);

				sgSendLog([
					'file' => __FILE__,
					'line' => __LINE__,
					'type' => 'Password request',
					'user' => i()->uid,
					'name' => $username,
					'description' => NULL,
				]);
			} catch (Exception $e) {
				// $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
				$message = 'ท่านได้ร้องขอรหัสผ่านจากเว็บ แต่ท่านไม่ได้ระบุอีเมล์ไว้ในข้อมูลของสมาชิก จึงไม่สามารถส่งข้อมูลให้ท่านทางอีเมล์ได้.';
				// $mail->ErrorInfo;
				$logMsg .= ' But email is invalid.';
			}
			// debugMsg($user, '$user');
			// debugMsg($mail, '$mail');
		}

		LogModel::save([
			'module' => 'user',
			'keyword' => 'Password request',
			'message' => $logMsg
		]);

		return $message;
	}

	private function sendMail_v1($username) {
		if (!load_lib('class.mail.php', 'lib')) return 'Mail module error';

		$logMsg = $username.' request new password.';

		$mail = new Mail();

		if ($mail->IsValidEmail($username)) { // input email
			mydb::where('`email` = :email', ':email', $username);
			$get_by_email = $username;
		} else { // input username
			mydb::where('`username` = :username', ':username', $username);
			$get_by_email = 'ตามที่ท่านได้ลงทะเบียนไว้ในชื่อ '.$username;
		}

		mydb::where('`status` = "enable"');

		$result = mydb::select('SELECT * FROM %users% %WHERE%');

		if ($result->_empty) return 'ไม่มีข้อมูลสมาชิกตามรายละเอียดที่ท่านระบุ';

		$password_detail = '';
		foreach ($result->items as $rs) {
			do {
				$token=md5(uniqid($username.rand(), true));
				$tokenrs=mydb::select('SELECT `code` FROM %users% WHERE `code`=:token LIMIT 1',':token',$token);
			} while (!$tokenrs->_empty);

			$url=_DOMAIN.url('user/resetpassword',array('sgpwcd'=>$token));
			$password_detail .= '<p>user name : '.$rs->username.'<br />';
			//$password_detail .= 'password : '.sg_decrypt($rs->password,cfg('encrypt_key')).'<br />';
			$password_detail .= 'email address : '.$rs->email.'</p>';
			$password_detail .= '<p>Please <a href="'.$url.'">click this link</a> or copy url below and paste into browser address bar</p> and following process. This link will expire within 60 minute.<br /><br />'.$url.'</p>';
			$mailto = $rs->email;

			// send mail
			$title = 'ท่านร้องขอรหัสผ่านจาก '.cfg('domain').' : Password request from '.cfg('domain');
			$from = 'noreply@'.cfg('domain.short');
			$from_name = 'noreply';
			$message = 'Password request from <strong>'.cfg('domain').'</strong><br /><br />'.$password_detail.'<hr />Please do not reply this mail';
			$mail->FromName($from_name);
			$mail->FromEmail($from);

			if ( $mailto ) {
				$mail_result = $mail->Send($mailto,$title,$message,false,'https://service.softganz.com');
				if ($mail_result) {
					$ret .= 'ได้ส่งข้อมูลไปให้ท่านที่อีเมล์ <strong>'.$get_by_email.'</strong> เรียบร้อยแล้ว :<br />กรุณาตรวจสอบอีเมล์ตามที่ท่านระบุไว้ในทะเบียนสมาชิก และคลิกลิงก์เพื่อเปลี่ยนรหัสผ่าน<br />หากยังไม่ได้รับอีเมล์ ให้ลองตรวจสอบใน <b>เมล์ขยะ (Spam mail)</b> ด้วยเพราะอาจจะถูกนำไปเก็บไว้ในนั้น';
					$logMsg.=' And password is send to email complete.';
					$stmt = 'UPDATE %users% SET `code`=:token, `pwresettime`=:now WHERE `uid`=:uid LIMIT 1';
					mydb::query($stmt,':token',$token, ':now',date('U'),':uid',$rs->uid);
				} else {
					$ret .= 'ไม่สามารถส่งอีเมล์ได้ในตอนนี้ กรุณาลองใหม่ในโอกาสต่อไป';
					// $ret.=message('error','ไม่สามารถส่งอีเมล์ได้ในตอนนี้ กรุณาลองใหม่ในโอกาสต่อไป');
					$logMsg.=' But email send was error.';
				}
				LogModel::save([
					'module' => 'user',
					'keyword' => 'Password request',
					'message' => $logMsg
				]);
			} else {
				$ret .= 'ท่านได้ร้องขอรหัสผ่านจากเว็บ แต่ท่านไม่ได้ระบุอีเมล์ไว้ในข้อมูลของสมาชิก จึงไม่สามารถส่งข้อมูลให้ท่านทางอีเมล์ได้.';
				// $ret .= message('error','ท่านได้ร้องขอรหัสผ่านจากเว็บ แต่ท่านไม่ได้ระบุอีเมล์ไว้ในข้อมูลของสมาชิก จึงไม่สามารถส่งข้อมูลให้ท่านทางอีเมล์ได้.');
				$logMsg.=' But email is invalid.';
				LogModel::save([
					'module' => 'user',
					'keyword' => 'Password request',
					'message' => $logMsg
				]);
			}
		}

		// if ($mail_result) {
			// $ret .= message([
			// 	'type' => 'status',
			// 	'text' => 'ได้ส่งข้อมูลไปให้ท่านที่อีเมล์ <strong>'.$get_by_email.'</strong> เรียบร้อยแล้ว : <ul><li>กรุณาตรวจสอบอีเมล์ตามที่ท่านระบุไว้ในทะเบียนสมาชิก และคลิกลิงก์เพื่อเปลี่ยนรหัสผ่าน</li><li>หากยังไม่ได้รับอีเมล์ ให้ลองตรวจสอบใน <b>เมล์ขยะ (Spam mail)</b> ด้วยเพราะอาจจะถูกนำไปเก็บไว้ในนั้น</li></ul>'
			// ]);
		// }

		return $ret;
	}
}
?>