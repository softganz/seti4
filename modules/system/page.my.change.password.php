<?php
/**
* My      :: Change Password Form
* Created :: 2021-08-23
* Modify  :: 2024-02-28
* Version :: 3
*
* @return Widget
*
* @usage my/change/password
*/

class MyChangePassword extends Page {
	var $cfgUserRegister;
	var $validCheck;

	function __construct() {
		parent::__construct([
			'cfgUserRegister' => $cfgUserRegister = cfg('user')->register,
			'validCheck' => explode(',', $cfgUserRegister->valid),
		]);
	}
	function build() {

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Change Password @'.i()->name,
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]), // AppBar
			'sideBar' => R()->appAgent ? NULL : R::View('my.menu'),
			'body' => new Form([
				'id' => 'my-change-password-form',
				'variable' => 'profile',
				'action' => url('api/my/password.change'),
				'class' => 'sg-form -sg-paddingnorm',
				'checkValid' => true,
				'rel' => 'notify',
				'done' => 'close | load',
				'children' => [
					'<div class="help">เปลี่ยนรหัสผ่านของคุณบ้าง เป็นการป้องกันไว้ก่อน</div>',
					'current' => [
						'type' =>'password',
						'label' => 'รหัสผ่านปัจจุบัน',
						'maxlength' => cfg('member.password.maxlength'),
						'class' => '-fill',
						'require' => true,
						'placeholder' => 'Enter current password',
						'description' => 'ป้อนรหัสผ่านที่ใช้งานอยู่ในปัจจุบัน',
						'posttext' => '<i class="icon -material -show-password" onClick=\'showPassword(this)\'>visibility_off</i>',
						'container' => ['class' => '-group'],
						'attribute' => ['onkeyup' => 'checkComplete.checkCurrentPasswordValid(this)'],
					],
					'password' => [
						'type' => 'password',
						'label' => 'รหัสผ่านใหม่',
						'maxlength' => cfg('member.password.maxlength'),
						'class' => '-fill',
						'require' => true,
						'placeholder' => 'Enter new password',
						'description' => 'ป้อนรหัสผ่านใหม่ที่ต้องการเปลี่ยน',
						'posttext' => '<i class="icon -material -show-password" onClick=\'showPassword(this)\'>visibility_off</i>',
						'container' => ['class' => '-group'],
						'attribute' => ['onkeyup' => 'checkComplete.checkPasswordValid(this)'],
					],
					'repassword' => [
						'type' => 'password',
						'label' => 'รหัสผ่านใหม่ (ยืนยัน)',
						'maxlength' => cfg('member.password.maxlength'),
						'class' => '-fill',
						'require' => true,
						'placeholder' => 'Re Enter new password',
						'description' => 'ป้อนรหัสผ่านใหม่อีกครั้งเพื่อยืนยันความถูกต้อง',
						'posttext' => '<i class="icon -material -show-password" onClick=\'showPassword(this)\'>visibility_off</i>',
						'container' => ['class' => '-group'],
						'attribute' => ['onkeyup' => 'checkComplete.checkRePasswordValid(this)'],
					],
					'submit' => [
						'type' => 'button',
						'class' => '-primary -save -disabled',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('my', ['closewebview'=>'YES']).'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => array('class' => '-sg-text-right'),
					],
					'<div class="help">เพื่อความรวดเร็ว ในการเปลี่ยน รหัสผ่าน กรุณาป้อนรหัสผ่านปัจจุบัน , รหัสผ่านใหม่ และ ยืนยันรหัสผ่านใหม่ ให้ถูกต้อง<br /><b>ข้อกำหนดของรหัสผ่าน :</b>
					<ul>'
					. (in_array('passwordMinLength', $this->validCheck) ? '<li>รหัสผ่านต้องมีความยาวอย่างน้อย <strong>'.$this->cfgUserRegister->passwordMinLength.' ตัวอักษร</strong></li>' : '')
					. (in_array('passwordContainNumeric', $this->validCheck) ? '<li>'.tr('Password must include at least one number').'</li>' : '')
					. (in_array('passwordContainLetter', $this->validCheck) ? '<li>'.tr('Password must include at least one letter').'</li>' : '')
					. (in_array('passwordContainUpperCase', $this->validCheck) ? '<li>'.tr('Password must include at least one upper case letter').'</li>' : '')
					. '</ul></div>',
					$this->script(),
				], // children
			]), // Widget
		]);
	}

	private function script() {
		return '<script type="text/javascript">
			checkComplete = new function() {
				let currentPasswordValid = false
				let passwordValid = false
				let rePasswordValid = false
				let checkValids = '.json_encode($this->validCheck).'

				function checkAllComplete() {
					let error = false
					let $saveButton = $("#my-change-password-form .btn.-save")

					// console.log("VALID ", "password",passwordValid)

					if (!currentPasswordValid || !passwordValid || !rePasswordValid) error = true

					// console.log("error", error)
					if (error) {
						$saveButton.addClass("-disabled")
					} else {
						$saveButton.removeClass("-disabled")
					}
				}

				this.checkCurrentPasswordValid = function(element) {
					let $this = $(element)
					let currentPassword = $this.val()
					let errors = []

					if (currentPassword.length === 0) errors.push("กรุณาป้อนรหัสผ่านปัจจุบัน")

					currentPasswordValid = errors.length === 0
					showErrors(element, errors)
					checkAllComplete()
				}

				this.checkPasswordValid = function(element) {
					let $this = $(element)
					let password = $this.val()
					let errors = []

					if (password.length === 0) {
						errors.push("กรุณาป้อนรหัสผ่านใหม่")
					} else {
						if (password.length < '.$this->cfgUserRegister->passwordMinLength.') errors.push("รหัสผ่านต้องมากกว่า '.$this->cfgUserRegister->passwordMinLength.' ตัวอักษร")
						if (checkValids.indexOf("passwordContainNumeric") && !password.match(/\d/)) errors.push("รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว")
						if (checkValids.indexOf("passwordContainLetter") && !password.match(/[a-zA-Zก-ฮ]/)) errors.push("รหัสผ่านต้องมีตัวอักษรอย่างน้อย 1 ตัว")
					}

					passwordValid = errors.length === 0
					showErrors(element, errors)
					checkAllComplete()
				}

				this.checkRePasswordValid = function(element) {
					let $this = $(element)
					let rePassword = $this.val()
					let errors = []

					if (rePassword != $("#edit-profile-password").val()) errors.push("ยืนยันรหัสผ่านไม่ตรงกัน")

					rePasswordValid = errors.length === 0
					showErrors(element, errors)
					checkAllComplete()
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

				function error2text(errors) {
					return "<ul><li>"+errors.join("</li><li>")+"</li></ul>"
				}
			}
			</script>';
	}
}
?>