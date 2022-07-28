<?php
/**
* User :: Register Form Setp 1
* Created 2019-05-06
* Modify  2020-10-22
*
* @param Array $register
* @return String
*/

$debug = true;

function view_user_register_form($register = []) {
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

	$form = new Form([
		'variable' => 'register',
		'action' => url(q()),
		'id' => 'edit-register',
		'class' => 'sg-form user-register-form',
		'title' => '<header class="header"><h3>'.tr('Register New Member').'</h3></header>',
		'checkValid' => true,
		'rel' => $register->rel ? $register->rel : NULL,
		'children' => [
			'rel' => $register->rel ? ['type' => 'hidden','value' => $register->rel] : NULL,
			'ret' => $register->ret ? ['type' => 'hidden','value' => $register->ret] : NULL,
			'step' => ['type' => 'hidden','value' => 1],

			($googleId = cfg('signin')->google->id) ? new Container([
				'class' => '-sg-text-center',
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
					'<div><span>หรือ</span><hr /></div>',
				], // children
			]) : NULL,

			'<fieldset id="account"><legend>ข้อมูลสมาชิก (Account information)</legend>',
			'username' => [
				'type' => 'text',
				'label' => 'ชื่อสมาชิก ( Username )',
				'maxlength' =>cfg('member.username.maxlength'),
				'class' => '-fill',
				'require' => true,
				'value' => $register->username,
				'placeholder' => 'username',
				'description' => cfg('member.username.format_text'),
				'attr' => array('style' => 'text-transform:lowercase;')
			],
			'password' => [
				'type' => 'password',
				'label' => 'รหัสผ่าน ( Password )',
				'maxlength' =>cfg('member.password.maxlength'),
				'class' => '-fill',
				'require' => true,
				'value' => $register->password,
				'placeholder' => 'password',
				'description' => 'รหัสผ่านต้องมีความยาวอย่างน้อย <strong>6 ตัวอักษร</strong>'
			],
			'repassword' => [
				'type' => 'password',
				'label' => 'ยืนยันรหัสผ่าน ( Re-enter Password )',
				'maxlength' =>cfg('member.password.maxlength'),
				'class' => '-fill',
				'require' => true,
				'value' => $register->repassword,
				'placeholder' => 're-password',
				'description' => 'ยืนยันรหัสผ่านอีกครั้งเพื่อความถูกต้องของการป้อนรหัสผ่าน'
			],
			'</fieldset>',
			'<fieldset id="personal"><legend>ข้อมูลส่วนบุคคล (Personal information)</legend>',
			'name' => [
				'type' => 'text',
				'label' => 'ชื่อสำหรับแสดง ( Name )',
				'maxlength' => 50,
				'class' => '-fill',
				'require' => true,
				'value' => $register->name,
				'placeholder' => 'ชื่อจริง',
				'description' => cfg('member.username.name_text')
			],
			'email' => [
				'type' => 'text',
				'label' => 'อีเมล์ ( E-Mail )',
				'maxlength' => 50,
				'class' => '-fill',
				'require' => true,
				'value' => $register->email,
				'description' => $emailDesc,
			],
			'</fieldset>',
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
						'class' => '-primary',
						'value' => '<i class="icon -material">navigate_next</i><span>{tr:Next}</span>'
					],
				],
				'container' => ['class' => '-sg-text-right'],
			],
			'help' => [
				'type' => 'textfield',
				'value' => '<strong>หมายเหตุ</strong> กรุณากรอกข้อมูลในช่องที่มีเครื่องหมาย * กํากับอยู่ให้ครบถ้วนสมบูรณ์'
			],
		], // children
	]);

	event_tricker('user.register_form', $self, $register, $form);

	$ret .= $form->build();

	$ret .= '<script type="text/javascript">
		$(document).ready(function() {
		$("#edit-register-username").keyup(function() {
			$("#edit-register-name").val($("#edit-register-username").val())
		})
	})
	</script>';

	return $ret;
}
?>