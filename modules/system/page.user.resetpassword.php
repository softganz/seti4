<?php
/**
 * User    :: Reset Password
 * Modify  :: 2025-06-25
 * Version :: 3
 *
 * @param String $args
 * @return Widget
 *
 * @usage user/resetpassword
 */

use Softganz\DB;

class UserResetpassword extends Page {
	var $token;
	var $cfgUserRegister;
	var $validCheck;

	function __construct($args = NULL) {
		parent::__construct([
			'token' => post('sgpwcd'),
			'cfgUserRegister' => $cfgUserRegister = cfg('user')->register,
			'validCheck' => explode(',', $cfgUserRegister->valid),
		]);
	}

	function rightToBuild() {
		if (empty($this->token)) return error(_HTTP_ERROR_NOT_FOUND, 'No Token');
	}

	function build() {
		$tokenexpire = date('U')-60*60; // expire in 60 min

		$user = DB::select([
			'SELECT `uid`, `username`, `name`, `code`, `pwresettime` FROM %users% WHERE `code` = :token LIMIT 1',
			'var' => [':token' => $this->token]
		]);

		if (empty($user->uid)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลตามที่ระบุ');
		if ($user->pwresettime < $tokenexpire) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'Password request time expired.');

		if (post('save')) return $this->saveNewPassword($user);

		LogModel::save([
			'module' => 'user',
			'keyword' => 'Password request click',
			'message' => 'Password request of '.$user->username.' was click.'
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Reset Password for '.$user->name,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					// new ListTile(['title' => 'Enter new password']),
					$error ? message('error', $error) : NULL,
					new Form([
						'variable' => 'resetpassword',
						'action' => url(q()),
						'id' => 'user-resetpassword',
						'class' => 'sg-form',
						'checkValid' => true,
						'children' => [
							'sgpwcd' => ['type' => 'hidden', 'name' => 'sgpwcd', 'value' => $this->token],
							'password' => [
								'type' => 'password',
								'label' => 'Enter new password',
								'maxlength' => 20,
								'class' => '-fill',
								'require' => true,
								'attribute' => ['onKeyUp' => 'checkComplete.checkPasswordValid(this)'],
								'placeholder' => 'new password',
								'posttext' => '<i class="icon -material -show-password" onClick=\'showPassword(this)\'>visibility_off</i>',
								'container' => ['class' => '-group'],
								'description' => ' ',
							],
							'repassword' => [
								'type' => 'password',
								'label' => 'RE-Enter new password',
								'maxlength' => 20,
								'class' => '-fill',
								'require' => true,
								'placeholder' => 'new password',
								'attribute' => ['onKeyUp' => 'checkComplete.checkRePasswordValid(this)'],
								'posttext' => '<i class="icon -material -show-password" onClick=\'showPassword(this)\'>visibility_off</i>',
								'container' => ['class' => '-group'],
								'description' => ' ',
							],
							'send' => [
								'type' => 'button',
								'name' => 'save',
								'class' => '-primary btn-confirm -disabled',
								'value' => '<i class="icon -material">done</i><span>Save new password</span>',
								'container' => ['class' => '-sg-text-right']
							],
						], // children
					'footer' => '<ul>'
						. (in_array('passwordMinLength', $this->validCheck) ? '<li>รหัสผ่านต้องมีความยาวอย่างน้อย <strong>'.$this->cfgUserRegister->passwordMinLength.' ตัวอักษร</strong></li>' : '')
						. (in_array('passwordContainNumeric', $this->validCheck) ? '<li>'.tr('Password must include at least one number').'</li>' : '')
						. (in_array('passwordContainLetter', $this->validCheck) ? '<li>'.tr('Password must include at least one letter').'</li>' : '')
						. (in_array('passwordContainUpperCase', $this->validCheck) ? '<li>'.tr('Password must include at least one upper case letter').'</li>' : '')
						. '</ul>',
					]), // Form
					$this->script(),
				], // children
			]), // Widget
		]);
	}

	private function saveNewPassword($user) {
		$post = (Object) post('resetpassword');

		if ($post->password === '') $error[] = 'กรุณาระบุ รหัสผ่าน (Password)'; //-- fill password
		if ($post->password && strlen($post->password) < 6) $error[]='รหัสผ่าน (Password) ต้องยาวอย่างน้อย 6 อักษร'; //-- password length
		if ($post->password && $post->password != $post->repassword) $error[]='กรุณายืนยันรหัสผ่าน (Re-enter password) ให้เหมือนกันรหัสที่ป้อน'; //-- password <> retype

		if ($error) return error(_HTTP_ERROR_NOT_ACCEPTABLE, $error);

		$password = sg_encrypt($post->password,cfg('encrypt_key'));

		DB::query([
			'UPDATE %users% SET `password` = :password, `code` = NULL, `pwresettime` = NULL WHERE `uid` = :uid LIMIT 1',
			'var' => [
				':uid' => $user->uid,
				':password' => $password
			]
		]);


		LogModel::save([
			'module' => 'user',
			'keyword' => 'Password request confirm',
			'message' => 'Password request of '.$user->username.' was changed.'
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Reset Password Complete',
			]),
			'body' => new Widget([
				'children' => [
					message('notify', 'บันทึกรหัสผ่านใหม่เรียบร้อย กรุณาเข้าสู่ระบบสมาชิกอีกครั้งด้วยรหัสใหม่'),
					new Center([
						'child' => new Button([
							'type' => 'primary',
							'href' => url('my'),
							'text' => 'เข้าสู่ระบบสมาชิกด้วยรหัสใหม่',
						]), // Button
					]), // Center
				]
			])
		]);
	}

	private function script() {
		return '<script type="text/javascript">
			checkComplete = new function() {
				let checkValids = '.json_encode($this->validCheck).'
				let passwordValid = false
				let rePasswordValid = false

				this.checkAllComplete = function () {
					let $btn = $(".btn-confirm")
					let error = false

					// console.log("VALID username",usernameValid, "password",passwordValid, "email",emailValid, "accept", acceptValid, "spamwordValid", spamwordValid)

					if (!passwordValid || !rePasswordValid) error = true

					// console.log("error", error)
					if (error) {
						$btn.addClass("-disabled")
					} else {
						$btn.removeClass("-disabled")
					}
				}

				function showErrors(element, errors = []) {
					let $this = $(element)
					let $errorEle = $this.closest(".form-item").find(".-error")

					if (errors.length === 0) {
						// No errors
						$errorEle.remove()
					} else if ($errorEle.length) {
						// Error element exists
						$errorEle.html(error2text(errors))
					} else {
						// Error element exists
						$this.closest(".form-item")
						.find(".description")
						.before($("<div>")
						.addClass("-error")
						.css("padding","8px")
						.html(error2text(errors)))
					}
				}

				function error2text(errors = []) {
					return "<ul><li>"+errors.join("</li><li>")+"</li></ul>"
				}

				this.checkPasswordValid = function (element) {
					let $this = $(element)
					let password = $this.val()

					if (!validPassword(password)) {
						// $this.focus()
						return false
					} else {
						showErrors(element)
					}
					this.checkAllComplete()

					function validPassword(password) {
						let passwordChar = /[a-zA-Zก-ฮ\!\@\#\$\%\^\&\*\(\)\_\+\-\=\{\}\[\]\|\:\"\;\\\'\<\>\?\,\.\\/\\\\]/
						let errors = []

						if (password.length == 0) return true

						// if (password.length < '.$this->cfgUserRegister->passwordMinLength.') errors.push("รหัสผ่านต้องมากกว่า '.$this->cfgUserRegister->passwordMinLength.' ตัวอักษร")
						if (password.length === 0) {
							errors.push("กรุณาป้อนรหัสผ่านใหม่")
						} else {
							if (password.length < '.$this->cfgUserRegister->passwordMinLength.') errors.push("รหัสผ่านต้องมากกว่า '.$this->cfgUserRegister->passwordMinLength.' ตัวอักษร")
							else if (checkValids.indexOf("passwordContainNumeric") && !password.match(/\d/)) errors.push("รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว")
							else if (checkValids.indexOf("passwordContainLetter") && !password.match(passwordChar)) errors.push("รหัสผ่านต้องมีตัวอักษรอย่างน้อย 1 ตัว")
						}

						if (errors.length === 0) {
							passwordValid = true
							return true
						} else {
							passwordValid = false
							showErrors(element, errors)
							return false
						}
					}
				}

				this.checkRePasswordValid = function(element) {
					let $this = $(element)
					let rePassword = $this.val()
					let errors = []

					if (rePassword != $("#edit-resetpassword-password").val()) errors.push("ยืนยันรหัสผ่านไม่ตรงกัน")

					rePasswordValid = errors.length === 0
					showErrors(element, errors)
					this.checkAllComplete()
				}

			}
			</script>';
	}
}
?>