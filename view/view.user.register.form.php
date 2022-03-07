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

function view_user_register_form($register = array()) {
	$form = new Form('register', url(q()), 'edit-register', 'sg-form user-register-form');
	$form->addConfig('title', '<header class="header"><h3>'.tr('Register New Member').'</h3></header>');
	$form->addData('checkValid', true);

	if ($register->rel) {
		$form->addData('rel',$register->rel);
		$form->addField('rel', array('type' => 'hidden','value' => $register->rel));
	}
	if ($register->ret) {
		$form->addField('ret', array('type' => 'hidden','value' => $register->ret));
	}


	$form->addField('step',array('type' => 'hidden','value' => 1));

	$form->addText('<fieldset id="account"><legend>ข้อมูลสมาชิก (Account information)</legend>');

	$form->addField(
		'username',
		array(
			'type' => 'text',
			'label' => 'ชื่อสมาชิก ( Username )',
			'maxlength' =>cfg('member.username.maxlength'),
			'class' => '-fill',
			'require' => true,
			'value' =>htmlspecialchars($register->username),
			'placeholder' => 'username',
			'description' => cfg('member.username.format_text'),
			'attr' => array('style' => 'text-transform:lowercase;')
		)
	);

	$form->addField(
		'password',
		array(
			'type' => 'password',
			'label' => 'รหัสผ่าน ( Password )',
			'maxlength' =>cfg('member.password.maxlength'),
			'class' => '-fill',
			'require' => true,
			'value' =>htmlspecialchars($register->password),
			'placeholder' => 'password',
			'description' => 'รหัสผ่านต้องมีความยาวอย่างน้อย <strong>6 ตัวอักษร</strong>'
		)
	);

	$form->addField(
		'repassword',
		array(
			'type' => 'password',
			'label' => 'ยืนยันรหัสผ่าน ( Re-enter Password )',
			'maxlength' =>cfg('member.password.maxlength'),
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($register->repassword),
			'placeholder' => 're-password',
			'description' => 'ยืนยันรหัสผ่านอีกครั้งเพื่อความถูกต้องของการป้อนรหัสผ่าน'
		)
	);

	$form->addText('</fieldset>');

	$form->addText('<fieldset id="personal"><legend>ข้อมูลส่วนบุคคล (Personal information)</legend>');

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อสำหรับแสดง ( Name )',
			'maxlength' => 50,
			'class' => '-fill',
			'require' => true,
			'value' =>htmlspecialchars($register->name),
			'placeholder' => 'ชื่อจริง',
			'description' => cfg('member.username.name_text')
		)
	);

	$emailDesc='<ul><li>กรุณาป้อนอี-เมล์ของท่านให้ถูกต้อง ทางเว็บไซท์จะไม่มีการแสดงอีเมล์นี้ของท่านในหน้าเว็บไซท์ แต่จะใช้ในกรณีดังต่อไปนี้<ol><li>ท่านลืมรหัสผ่าน ระบบจะส่งรหัสผ่านไปให้ท่านตามอีเมล์ที่ระบุนี้</li><li>มีการติดต่อจากแบบฟอร์มที่ให้กรอกในหน้าเว็บไซท์เพื่อส่งถึงท่าน</li></ol></li>';

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

	$form->addField(
		'email',
		array(
			'type' => 'text',
			'label' => 'อีเมล์ ( E-Mail )',
			'maxlength' => 50,
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($register->email),
			'description' => $emailDesc,
		)
	);

	$form->addText('</fieldset>');

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'items' => array(
				'cancel' => array(
					'type' => 'cancel',
					'class' => '-link',
					'value' => '<i class="icon -cancel -gray"></i><span>{tr:Cancel}</span>'
				),
				'next' => array(
					'type' => 'submit',
					'class' => '-primary',
					'value' => '<i class="icon -forward -white"></i><span>{tr:Next}</span>'
				),
			),
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$form->addField(
		'help',
		array(
			'type' => 'textfield',
			'value' => '<strong>หมายเหตุ</strong> กรุณากรอกข้อมูลในช่องที่มีเครื่องหมาย * กํากับอยู่ให้ครบถ้วนสมบูรณ์'
		)
	);

	event_tricker('user.register_form',$self,$register,$form);

	$ret .= $form->build();

	$ret .= '<script type="text/javascript">
		$(document).ready(function() {
		$("#edit-register-username").keyup(function() {
			$("#edit-register-name").val($("#edit-register-username").val());
		});
		});
	</script>';

	return $ret;
}
?>