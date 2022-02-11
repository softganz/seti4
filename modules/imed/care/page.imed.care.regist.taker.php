<?php
/**
* iMed Care :: Taker Register
* Created 2021-07-22
* Modify  2021-12-22
*
* @return Widget
*
* @usage imed/care/regist/taker
*/

import('model:user.php');
import('package:imed/models/model.imed.user.php');

class ImedCareRegistTaker extends Page {
	function build() {
		$userInfo = new ImedUserModel(['role' => 'IMED TAKER']);
		$isWaiting = $userInfo->isWaiting();
		$isEnable = $userInfo->isEnable();

		// debugMsg($userInfo, '$userInfo');

		if ($userInfo->isRole()) location('imed/care/taker');

		if (post('data')) return $this->_save((Object) post('data'));

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สมัครเป็นผู้รับบริการ',
				'removeOnApp' => true,
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'variable' => 'data',
						'action' => url('imed/care/regist/taker'),
						'class' => 'sg-form',
						'checkValid' => true,
						'rel' => 'notify',
						'done' => 'reload:'.url('imed/care/taker'),
						'children' => [
							'<div class="widget-card">',
							new ListTile([
								'class' => '-sg-paddingmore',
								'title' => 'ข้อมูลสำหรับเข้าสู่ระบบสมาชิก',
								'leading' => '<i class="icon -material">key</i>',
							]),
							!i()->ok ? [
								'children' => [
									'username' => [
										'label' => 'ชื่อสมาชิก (Username)',
										'type' => 'text',
										'class' => '-fill',
										'require' => true,
										'placeholder' => 'Username (สำหรับเข้าสู่ระบบสมาชิก)',
										'attr' => ['style' => 'text-transform:lowercase;'],
										'description' => 'อย่างต่ำ 4 ตัวอักษร เฉพาะ a-z 0-9 . - เท่านั้น',
									],
									'password' => [
										'label' => 'รหัสผ่าน (Password)',
										'type' => 'password',
										'class' => '-fill',
										'require' => true,
										'placeholder' => 'รหัสผ่าน',
										'description' => 'รหัสผ่านอย่างน้อย 6 ตัวอักษร',
									],
									'rePassword' => [
										'label' => 'ยืนยันรหัสผ่าน (Confirm Password)',
										'type' => 'password',
										'class' => '-fill',
										'require' => true,
										'maxlength' => cfg('member.password.maxlength'),
										'placeholder' => 'ยืนยันรหัสผ่าน',
									],
								]
							] : [
								'children' => [
									['type' => 'textfield', 'label' => 'Username : '.i()->username],
									['type' => 'textfield', 'label' => 'อีเมล์ (Email) : '.$userInfo->email],
								],
							],
							'</div>',

							'<div class="widget-card">',
							new ListTile([
								'class' => '-sg-paddingmore',
								'title' => 'ข้อมูลส่วนบุคคล',
								'leading' => '<i class="icon -material">account_circle</i>',
							]),
							'name' => [
								'label' => 'ชื่อ-นามสกุล (Full Name)',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
								'value' => $userInfo->fullName,
								'placeholder' => 'ชื่อ นามสกุล',
							],
							'phone' => [
								'label' => 'โทรศัพท์มือถือ (Mobille Phone)',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
								'value' => $userInfo->phone,
								'placeholder' => '000 000 0000',
							],
							'email' => !i()->ok ? [
								'label' => 'อีเมล์ (Email)',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
								'placeholder' => 'อีเมล์ (สำหรับเข้าสู่ระบบสมาชิก)',
								'description' => 'ท่านสามารถใช้อีเมล์ในการเข้าสู่ระบบสมาชิกได้',
								'attr' => ['style' => 'text-transform:lowercase;'],
								// 'description' => 'ผู้ดูแลระบบจะจัดส่ง username และ password ให้ทางอีเมล์ที่ระบุไว้'
							] : NULL,
							'</div>',
							'accept' => [
								'type' => 'checkbox',
								'require' => true,
								// 'options' => ['yes' => 'I accept the Terms of Use and Privacy Policy.'],
								'options' => ['yes' => 'ยอมรับข้อตกลงและเงื่อนไขการใช้บริการเว็บไซต์'],
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>สมัครใช้บริการ</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]), // Form
				], // children
			]), // Widget
		]);
	}

	function _save($data) {
		if (!i()->ok) {
			if (empty($data->username))
				return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุชื่อสมาชิก (Username)']);
			else if (UserModel::get(['username' => $data->username]))
				return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'Username : '.$data->username.' มีผู้อื่นใช้งานไปแล้ว กรุณาใช้ชื่อใหม่']);
			else if (empty($data->password))
				return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุรหัสผ่าน (Password)']);
			else if ($data->rePassword && $data->password != $data->rePassword)
				return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'รหัสผ่าน ไม่ตรงกับ ยืนยันรหัสผ่าน']);
			else if ($data->password && strlen($data->password) < 6)
				return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุรหัสผ่านอย่างน้อย 6 ตัวอักษร']);
			else if (empty($data->email))
				return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุอีเมล์ (E-mail)']);
			else if ($data->email && !sg_is_email($data->email))
				return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'อีเมล์ (E-mail) ไม่ถูกต้อง']);
			else if ($data->reEmail && $data->email != $data->reEmail)
				return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'อีเมล์ ไม่ตรงกับ ยืนยันอีเมล์']);
			else if ($data->email && UserModel::get(['email' => $data->email]))
				return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'อีเมล์ <strong><em>'.$data->email.'</em></strong> ได้มีการลงทะเบียนไว้แล้ว ไม่สามารถใช้ซ้ำได้']);
		}

		if (empty($data->name))
			return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุชื่อ นามสกุล']);
		else if (empty($data->phone))
			return message(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุหมายเลขโทรศัพท์']);

		list($data->realName, $data->lastName) = sg::explode_name(' ', $data->name);

		$userInfo = (Object) [
			'username' => $data->username,
			'password' => $data->password,
			'name' => $data->name,
			'email' => $data->email,
			'phone' => $data->phone,
			'realName' => $data->realName,
			'lastName' => $data->lastName,
			'admin_remark' => 'Register from imed/care/regist/taker',
			'roles' => (Object) [
				'role' => 'IMED TAKER',
				'status' => 'ENABLE',
			],
		];

		if ($data->username) {
			$result = UserModel::create($userInfo);
			// debugMsg($result, '$result');
			if ($result->complete) {
				UserModel::signInProcess($userInfo->username,$userInfo->password);
			}
		} else {
			$userInfo->userId = i()->uid;
			mydb::query(
				'UPDATE %users% SET
				`real_name` = :realName, `last_name` = :lastName, `phone` = :phone
				WHERE `uid` = :userId LIMIT 1',
				$userInfo
			);
			// debugMsg(mydb()->_query);

			mydb::query(
				'INSERT INTO %users_role%
				(`uid`, `role`, `status`, `created`)
				VALUES
				(:userId, :role, :status, :created)
				ON DUPLICATE KEY UPDATE
				`uid` = :userId
				',
				[
					':userId' => i()->uid,
					':role' => $userInfo->roles->role,
					':status' => $userInfo->roles->status,
					':created' => date('U'),
				]
			);
			// debugMsg(mydb()->_query);
		}

		return $error ? $error : 'Taker Register Complete';
	}
}
?>