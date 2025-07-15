<?php
/**
* User    :: Register Form
* Created :: 2019-05-06
* Modify  :: 2025-07-15
* Version :: 8
*
* @param Object $register
* @return Widget
*
* @usage import('widget:user.register.form.php')
* @usage new UserRegisterFormWidget([])
*/

class UserRegisterFormWidget extends Widget {
	protected $captchaKey;
	var $register;
	var $cfgUserRegister;
	var $validCheck;

	function __construct($register = []) {
		parent::__construct([
			'captchaKey' => cfg('captcha'),
			'register' => (Object) $register,
			'cfgUserRegister' => $cfgUserRegister = cfg('user')->register,
			'validCheck' => explode(',', $cfgUserRegister->valid),
		]);
	}

	function build() {
		if ($this->captchaKey) head(' <script src="https://www.google.com/recaptcha/api.js"></script>');

			$emailDesc = '<ul><li>กรุณาป้อนอี-เมล์ของท่านให้ถูกต้อง ทางเว็บไซท์จะไม่มีการแสดงอีเมล์นี้ของท่านในหน้าเว็บไซท์ แต่จะใช้ในกรณีดังต่อไปนี้<ol><li>ท่านลืมรหัสผ่าน ระบบจะส่งรหัสผ่านไปให้ท่านตามอีเมล์ที่ระบุนี้</li><li>มีการติดต่อจากแบบฟอร์มที่ให้กรอกในหน้าเว็บไซท์เพื่อส่งถึงท่าน</li></ol></li>';

		switch (cfg('member.registration.method')) {
			case 'email' :
				$emailDesc .= '<li><strong>เมื่อท่านลงทะเบียนเรียบร้อย เราจะส่งอี-เมล์ถึงท่าน ตามอี-เมล์ที่ท่านระบุ และท่านจะต้องทำการยืนยันการเป็นสมาชิก การสมัครสมาชิกจึงจะสมบูรณ์</strong></li>';
				break;
			case 'waiting' :
				$emailDesc .= '<li><strong>เมื่อท่านลงทะเบียนเรียบร้อย กรุณารอจนกว่าผู้ดูแลเว็บไซท์กำหนดสิทธิ์ให้เริ่มใช้งานได้</strong></li>';
				break;
			case 'waiting,email' :
				$emailDesc .= '<li><strong>เมื่อท่านลงทะเบียนเรียบร้อย กรุณารอจนกว่าผู้ดูแลเว็บไซท์กำหนดสิทธิ์ให้เริ่มใช้งานได้</strong></li><li><strong>เมื่อผู้ดูแลระบบกำหนดสิทธิ์เรียบร้อย เราจะส่งอี-เมล์ถึงท่าน ตามอี-เมล์ที่ท่านระบุ และท่านจะต้องทำการยืนยันการเป็นสมาชิก การสมัครสมาชิกจึงจะสมบูรณ์</strong></li>';
				break;
		}

		$emailDesc .= '</ul>';

		return new Form([
			'variable' => 'register',
			'action' => url('user/register..save'),
			'id' => 'edit-register',
			'class' => 'x-sg-form user-register-form',
			// 'title' => '<header class="header"><h3>'.tr('Register New Member').'</h3></header>',
			'checkValid' => true,
			'rel' => SG\getFirst($this->register->rel, 'edit-register'),
			'attribute' => ['onSubmit' => 'checkComplete.registerSubmit(this)'],
			'children' => [
				'rel' => $this->register->rel ? ['type' => 'hidden','value' => $this->register->rel] : NULL,
				'ret' => $this->register->ret ? ['type' => 'hidden','value' => $this->register->ret] : NULL,
				'step' => ['type' => 'hidden','value' => 1],

				($googleId = cfg('signin')->google->id) ? new Container([
					'class' => '-sg-text-center -sg-paddingmore',
					'children' => [
						new Row([
							'mainAxisAlignment' => 'center',
							// 'class' => '-sg-text-center',
							'children' => [
								'<script src="https://accounts.google.com/gsi/client" async defer></script>
								<div id="g_id_onload"
									data-client_id="'.$googleId.'"
									data-login_uri="'._DOMAIN.url('user/register', ['signWith' => 'google']).'"
									data-auto_prompt="false"
									data-ux_mode="redirect"
									>
								</div>
								<div class="g_id_signin"
									data-type="standard"
									data-size="large"
									data-theme="filled_blue"
									data-text="signup_with"
									data-context="signup"
									data-shape="circle"
									data-logo_alignment="left"
									>
								</div>',
							], // children
						]), // Row
						'<div>หรือ</div>',
						// '<hr />',
					], // children
				]) : NULL,

				new ListTile([
					'crossAxisAlignment' => 'center',
					'title' => tr('Register New Member'),
					'leading' => new Icon('person_add_alt'),
				]),
				'<fieldset id="account" class="user-register-form-account"><legend>ข้อมูลสมาชิก (Account information)</legend>',
				'username' => [
					'type' => 'text',
					'label' => 'ชื่อสมาชิก ( Username )',
					'maxlength' =>cfg('member.username.maxlength'),
					'class' => '-fill -check-key-up',
					'require' => true,
					'value' => $this->register->username,
					'placeholder' => 'username',
					'description' => cfg('user')->register->usernameMatchText,
					'attribute' => ['style' => 'text-transform:lowercase;', 'onKeyUp' => 'checkComplete.checkUserValid(this)', 'onblur' => 'checkComplete.checkUserValid(this)']
				],
				'password' => [
					'type' => 'password',
					'label' => 'รหัสผ่าน ( Password )',
					'maxlength' =>cfg('member.password.maxlength'),
					'class' => '-fill',
					'require' => true,
					'value' => $this->register->password,
					'placeholder' => 'password',
					'attribute' => ['onKeyUp' => 'checkComplete.checkPasswordValid(this)'],
					'description' => '<ul>'
						. (in_array('passwordMinLength', $this->validCheck) ? '<li>รหัสผ่านต้องมีความยาวอย่างน้อย <strong>'.$this->cfgUserRegister->passwordMinLength.' ตัวอักษร</strong></li>' : '')
						. (in_array('passwordContainNumeric', $this->validCheck) ? '<li>'.tr('Password must include at least one number').'</li>' : '')
						. (in_array('passwordContainLetter', $this->validCheck) ? '<li>'.tr('Password must include at least one letter').'</li>' : '')
						. (in_array('passwordContainUpperCase', $this->validCheck) ? '<li>'.tr('Password must include at least one upper case letter').'</li>' : '')
						. '</ul>',
					'posttext' => '<i class="icon -material -show-password" onClick=\'showPassword(this)\'>visibility_off</i>',
					'container' => ['class' => '-group'],
				],
				'repassword' => [
					'type' => 'password',
					'label' => 'ยืนยันรหัสผ่าน ( Re-enter Password )',
					'maxlength' =>cfg('member.password.maxlength'),
					'class' => '-fill',
					'require' => true,
					'value' => $this->register->repassword,
					'attribute' => ['onKeyUp' => 'checkComplete.checkRePasswordValid(this)'],
					'placeholder' => 're-password',
					'description' => 'ยืนยันรหัสผ่านอีกครั้งเพื่อความถูกต้องของการป้อนรหัสผ่าน',
					'posttext' => '<i class="icon -material -show-password" onClick=\'showPassword(this)\'>visibility_off</i>',
					'container' => ['class' => '-group'],
				],
				'</fieldset>',
				'<fieldset id="personal" class="user-register-form-personal"><legend>ข้อมูลส่วนบุคคล (Personal information)</legend>',
				'name' => [
					'type' => 'text',
					'label' => 'ชื่อสำหรับแสดง ( Name )',
					'maxlength' => 50,
					'class' => '-fill',
					'require' => true,
					'value' => $this->register->name,
					'placeholder' => 'ระบุชื่อจริงสำหรับนำไปแสดง',
					'description' => cfg('member.username.name_text')
				],
				'email' => [
					'type' => 'text',
					'label' => 'อีเมล์ ( E-Mail )',
					'maxlength' => 50,
					'class' => '-fill -check-key-up',
					'require' => true,
					'value' => $this->register->email,
					'description' => $emailDesc,
					'placeholder' => 'name@example.com',
					'attribute' => ['style' => 'text-transform:lowercase;', 'onKeyUp' => 'checkComplete.checkEmailValid(this)']
				],
				'</fieldset>',
				'accept' => [
					'type' => 'checkbox',
					'options' => ['yes' => '<b>ฉันเข้าใจและยอมรับข้อตกลงรวมทั้งเงื่อนไขในการใช้บริการ <a href="'.url('privacy').'" target="_blank" data-webview="เงื่อนไขการใช้งาน">รายละเอียดเงื่อนไข</a></b>'],
					'description' => 'ยอมรับข้อตกลงการใช้งาน',
					'attribute' => ['onChange' => 'checkComplete.checkAccept(this)']
				],
				'verify' => [
					'type' => 'hidden',
					'label' => 'Verify your account',
					'require' => true,
					'pretext' => '<em id="spamword" class="spamword"></em> ',
					'placeholder' => 'พิมพ์อักขระที่ปรากฎด้านหน้าของช่อง',
					'description' => 'ท่านจำเป็นต้องป้อนตัวอักษรของ Anti-spam word ในช่องข้างบนให้ถูกต้อง',
					'attribute' => ['onKeyUp' => 'checkComplete.checkAllComplete(this)']
				],
				'submit' => [
					'type' => 'button',
					'items' => [
						'cancel' => [
							'type' => 'cancel',
							'class' => '-link',
							'value' => '<i class="icon -material -gray">cancel</i><span>{tr:Cancel}</span>'
						],
						'next' => [
							'type' => 'submit',
							'class' => '-primary -next -disabled g-recaptcha',
							'value' => '<i class="icon -material">navigate_next</i><span><b>{tr:Sign up now}</b></span>',
							'attribute' => [
								'data-sitekey' => $this->captchaKey,
								'data-callback' => 'onSubmit',
								'data-action' => 'submit'
							]
						],
					],
					'container' => ['class' => '-sg-text-right'],
				],
				'help' => [
					'type' => 'textfield',
					'value' => '<strong>หมายเหตุ</strong> กรุณากรอกข้อมูลในช่องที่มีเครื่องหมาย * กํากับอยู่ให้ครบถ้วนสมบูรณ์'
				],
				$this->script(),
			], // children
		]);

		// event_tricker('user.register_form', $self, $this->register, $form);
	}

	private function script() {
		return '<script type="text/javascript">
			function onSubmit(token) {
				// console.log("Submit", token);
				// document.getElementById("edit-register").submit();
				checkComplete.registerSubmit($("#edit-register"));
			}

			checkComplete = new function() {
				let checkValids = '.json_encode($this->validCheck).'
				let usernameValid = true
				let passwordValid = true
				let emailValid = true

				this.checkAllComplete = function () {
					let $btn = $(".btn.-next")
					let error = false
					let acceptValid = $("#edit-register-accept-yes").is(":checked")
					let spamwordValid = $("#spamword").text() != "" && $("#spamword").text() === $("#edit-register-verify").val()

					// console.log("VALID username",usernameValid, "password",passwordValid, "email",emailValid, "accept", acceptValid, "spamwordValid", spamwordValid)

					if (!usernameValid || !passwordValid || !emailValid || !acceptValid || !spamwordValid) error = true

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

				function userExists(para) {
					let exists = false
					jQuery.ajaxSetup({async:false});

					// $.ajax({
					// 	type: "GET",
					// 	url: SG.url("api/user/exists"),
					// 	async: false,
					// 	data: para,
					// 	error: function(response) {
					// 		console.clear()
					// 		// console.log("response ERROR", response)
					// 		exists = response.responseJSON.text
					// 	}
					// });
					$.get(
						SG.url("api/user/exists"),
						para,
					).fail(function(response) {
						console.clear()
						// console.log("response ERROR", response)
						exists = response.responseJSON.text
					});
					// .done(function(data) {
					// 	// console.log("DONE", data)
					// 	exists = data.text
					// });

					return exists
				}

				this.checkUserValid = function (element) {
					let $this = $(element)
					let username = $this.val().toLowerCase()
					let errors = []
					const usernameRegex = /[a-z0-9\.\-\_]/

					// console.log("username", username)

					if (username.match(usernameRegex)) {
						username = username.replace(/ /g, "")
						$this.val(username)
					}

					$("#edit-register-name").val(username)

					if (username === "") {
						usernameValid = false
						showErrors(element, ["ยังไม่มีการป้อนข้อมูล"])
						this.checkAllComplete()
						return true
					} else if (username.length < 4) {
						errors.push("ชื่อสมาชิกน้อยกว่า 4 ตัวอักษร")
					} else {
						let exists = userExists({name: username})
						if (exists === false) {} else errors.push(exists)
					}

					if (errors.length === 0) {
						usernameValid = true
						showErrors(element)
					} else {
						usernameValid = false
						showErrors(element, errors)
						$this.focus()
					}
					this.checkAllComplete()
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

					if (rePassword != $("#edit-register-password").val()) errors.push("ยืนยันรหัสผ่านไม่ตรงกัน")

					rePasswordValid = errors.length === 0
					showErrors(element, errors)
					this.checkAllComplete()
				}

				this.checkEmailValid = function (element) {
					let $this = $(element)
					let email = $this.val()
					let errors = []
					const validRegex = /^[a-zA-Z0-9.!#$%&\'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/

					// console.log("email", email)
					if (email === "") {
						emailValid = false
						showErrors(element, ["ยังไม่มีการป้อนข้อมูล"])
						this.checkAllComplete()
						return true
					} else if (!email.match(validRegex)) {
						// TODO: check email format
						errors.push("รูปแบบของอีเมล์ไม่ถูกต้อง")
					} else {
						let exists = userExists({email: email})
						if (exists === false) {} else errors.push(exists)
					}

					if (errors.length === 0) {
						emailValid = true
						showErrors(element)
					} else {
						emailValid = false
						showErrors(element, errors)
						$this.focus()
					}
					this.checkAllComplete()
				}

				this.checkAccept = function (element) {
					let acceptChecked = $(element).is(":checked")
					let $verifyElement = $("#form-item-edit-register-verify")
					let $spamwordElement = $("#spamword")

					if (acceptChecked) {
						$verifyElement
						.css("display", "flex")
						.find("input").attr("type", "text");

						// console.log(SG.url("user/register..verify"));

						// Check spam word
						if (!$("#spamword").text()) {
							jQuery.ajaxSetup({async:false});
							$.get(
								SG.url("user/register..verify"),
							).fail(function(response) {
								// console.clear();
								// console.log("response ERROR", response);
								// statusText
								exists = response.responseJSON.text
							})
							.done(function(data) {
								// console.log(data);
								$("#spamword").text(data)
							});
								// $.ajax({
								// 	type: "GET",
								// 	url: SG.url("user/register..verify"),
								// 	async: false,
								// 	error: function(response) {
								// 		// console.clear();
								// 		console.log("response ERROR", response);
								// 		// statusText
								// 		// exists = response.responseJSON.text								
								// 	}
								// })
								// .done(function(data) {
								// 	console.log("VERIFY DONE", data)
								// 	$("#spamword").text(data)
								// })
						}
					} else {
						$verifyElement.hide()
					}

					// console.log($(element).is(":checked"))
					this.checkAllComplete()
				}

				this.registerSubmit = function (formElement) {
					let $formElement = $(formElement)
					let para = {}
					$formElement.serialize()
					// console.log($formElement.serialize())

					// console.log("SUBMIT", $formElement.serialize())
					$.post($formElement.attr("action"), $formElement.serialize())
					.done(function(response){
						// console.log("DONE", response)
						let location = response.location
						if (location === undefined || location === "") location = "my"
						if (!location.match(/^\//)) location = SG.url(location)
						// console.log("location", location)
						window.location = location
					})
					.fail(function(response){
						console.clear()
						console.log("FAIL", response)

						Object.keys(response.responseJSON).forEach( function(key) {
							let errorText = response.responseJSON[key]
							// console.log(key, errorText)
							switch (key) {
								case "username":
									showErrors(document.getElementById("edit-register-username"), errorText)
									break;
								case "password":
									showErrors(document.getElementById("edit-register-password"), errorText)
									break;
								case "repassword":
									showErrors(document.getElementById("edit-register-repassword"), errorText)
									break;
								case "name":
									showErrors(document.getElementById("edit-register-name"), errorText)
									break;
								case "email":
									showErrors(document.getElementById("edit-register-email"), errorText)
									break;
								case "accept":
									showErrors(document.getElementById("edit-register-accept-yes"), errorText)
									break;
								case "submit":
									showErrors(document.getElementById("edit-register-verify"), errorText)
									break;
							}
						});
					})
					event.preventDefault()
					return false
				}
			}
			</script>

			<style>
			.form-item.-edit-register-verify {display: hidden; flex-wrap: wrap; gap: 8px;}
			.form-item.-edit-register-verify>label {flex: 0 0 100%;}
			.form-item.-edit-register-verify>input {flex: 1;}
			.form-item.-edit-register-verify>div {flex: 0 0 100%;}
			.user-register-form .form-item>.description {display: none;}
			.user-register-form .form-item:focus-within>.description {display: block;}
			</style>';
	}
}
?>