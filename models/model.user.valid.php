<?php
/**
* User    :: Check User Valid
* Created :: 2024-02-27
* Modify  :: 2024-02-27
* Version :: 1
*
* @param Array $args
* @return Object
*
* @usage import('model:user.valid.php')
* @usage new UserValidModel([])
* @usage UserValidModel::function($conditions)
*/

class UserValidModel {
	public static function checkPasswordValid($pwd, &$errors) {
		$errors_init = $errors;
		$cfgUserRegister = cfg('user')->register;
		$validCheck = explode(',', $cfgUserRegister->valid);

		// passwordMinLength,passwordMaxLength,passwordContainNumeric
		// $errors[] = 'password = '.$pwd;
		// $errors[] = 'passwordMinLength = '.$cfgUserRegister->passwordMinLength;
		if (in_array('passwordMinLength', $validCheck) && strlen($pwd) < $cfgUserRegister->passwordMinLength) {
			$errors[] = 'รหัสผ่านต้องมีความยาวอย่างน้อย '.$cfgUserRegister->passwordMinLength.' ตัวอักษร'; //"Password too short!";
		}

		if (in_array('passwordMaxLength', $validCheck) && strlen($pwd) > $cfgUserRegister->passwordMaxLength) {
			$errors[] = 'รหัสผ่านต้องมีความยาวไม่เกิน '.$cfgUserRegister->passwordMaxLength.' ตัวอักษร'; //"Password too long!";
		}

		if (in_array('passwordContainNumeric', $validCheck) && !preg_match('/[0-9]+/', $pwd)) {
			$errors[] = tr('Password must include at least one number').'!';
		}

		if (in_array('passwordContainLetter', $validCheck) && !preg_match('/[a-zA-Zก-ฮ]+/', $pwd)) {
			$errors[] = tr('Password must include at least one letter').'!';
		}

		if (in_array('passwordContainUpperCase', $validCheck) && !preg_match('/[A-Z]+/', $pwd)) {
			$errors[] = tr('Password must include at least one upper case letter').'!';
		}

		// if (in_array('passwordContainNoneLetter', $validCheck) && !preg_match('/[A-Z]+/', $pwd)) {
		// 	$errors[] = tr('Password must include at least one upper case letter').'!';
		// }

		return ($errors == $errors_init);
	}

	public static function validUsername($username, $pattern = NULL) {
		if (is_null($pattern)) $pattern = cfg('user')->register->usernameMatch;

		$result = true;

		if (strlen($username) < 4) {
			//-- username length
			$result = 'ชื่อสมาชิก (Username) อย่างน้อย 4 อักษร';
		} else if (!preg_match($pattern, $username)) {
			//-- check valid char
			$result = 'ชื่อสมาชิก (Username) <strong><em>'.$username.'</em></strong> มีอักษรหรือความยาวไม่ตรงตามเงื่อนไข';
		} else if (mydb::select(
				'SELECT `username` FROM %users% WHERE `username` = :username LIMIT 1;
				-- {reset: false}',
				[':username' => $username]
			)->username) {
			//-- duplicate username
			$result = 'ชื่อสมาชิก (Username) <strong><em>'.$username.'</em></strong> มีผู้อื่นใช้ไปแล้ว กรุณาใช้ชื่อใหม่';
		}
		return $result;
	}

	public static function checkRePasswordValid($password, $rePassword, &$errors) {
		$errors_init = $errors;

		if ($password && $password != $rePassword) $errors[] = 'กรุณายืนยันรหัสผ่าน (Re-enter password) ให้เหมือนกันรหัสที่ป้อน'; //-- password <> retype
		return ($errors == $errors_init);
	}
}
?>