<?php
/**
* My      :: Change Password Form
* Created :: 2021-08-23
* Modify  :: 2023-07-11
* Version :: 2
*
* @return Widget
*
* @usage my/change/password
*/

class MyChangePassword extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Change Password @'.i()->name,
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]), // AppBar
			'sideBar' => R()->appAgent ? NULL : R::View('my.menu'),
			'body' => new Form([
				'variable' => 'profile',
				'action' => url('api/my/password.change'),
				'class' => 'sg-form',
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
					],
					'password' => [
						'type' => 'password',
						'label' => 'รหัสผ่านใหม่',
						'maxlength' => cfg('member.password.maxlength'),
						'class' => '-fill',
						'require' => true,
						'placeholder' => 'Enter new password',
						'description' => 'ป้อนรหัสผ่านใหม่ที่ต้องการเปลี่ยน',
					],
					'repassword' => [
						'type' => 'password',
						'label' => 'รหัสผ่านใหม่ (ยืนยัน)',
						'maxlength' => cfg('member.password.maxlength'),
						'class' => '-fill',
						'require' => true,
						'placeholder' => 'Re Enter new password',
						'description' => 'ป้อนรหัสผ่านใหม่อีกครั้งเพื่อยืนยันความถูกต้อง',
					],
					'submit' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('my', ['closewebview'=>'YES']).'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => array('class' => '-sg-text-right'),
					],
					'<div class="help">เพื่อความรวดเร็ว ในการเปลี่ยน รหัสผ่าน กรุณาป้อนรหัสผ่านปัจจุบัน , รหัสผ่านใหม่ และ ยืนยันรหัสผ่านใหม่ ให้ถูกต้อง</div>',
				], // children
			]), // Widget
		]);
	}
}
?>