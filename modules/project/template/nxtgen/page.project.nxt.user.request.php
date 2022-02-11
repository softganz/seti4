<?php
/**
* Project Nxt :: News Main Page
* Created 2021-10-31
* Modify  2021-10-31
*
* @return Widget
*
* @usage project/nxt/news
*/

$debug = true;

import('model:user.php');
import('model:org.php');

class ProjectNxtUserRequest extends Page {
	function build() {
		if (post('data')) return $this->_saveRequest();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สมัครใช้บริการ',
				'boxHeader' => true,
				// 'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'variable' => 'data',
						'action' => url('project/nxt/user/request'),
						'class' => 'sg-form',
						'checkValid' => true,
						'rel' => 'box',
						'done' => 'load:#main:'.url('project/nxt'),
						'attribute' => ['data-width' => '320'],
						'children' => [
							new ListTile([
								'class' => '-sg-paddingmore',
								'title' => 'ข้อมูลสำหรับเข้าสู่ระบบสมาชิก',
								'leading' => '<i class="icon -material">key</i>',
							]),
							'email' => [
								'label' => 'อีเมล์ (Email)',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
								'placeholder' => 'อีเมล์ (สำหรับเข้าสู่ระบบสมาชิก)',
								'description' => 'ท่านสามารถใช้อีเมล์ในการเข้าสู่ระบบสมาชิกได้',
								'attr' => ['style' => 'text-transform:lowercase;'],
								// 'description' => 'ผู้ดูแลระบบจะจัดส่ง username และ password ให้ทางอีเมล์ที่ระบุไว้'
							],
							// 'reEmail' => [
							// 	'label' => 'ยืนยันอีเมล์ (Confirm Email)',
							// 	'type' => 'text',
							// 	'class' => '-fill',
							// 	'require' => true,
							// 	'placeholder' => 'อีเมล์',
							// 	'description' => 'ผู้ดูแลระบบจะจัดส่ง username และ password ให้ทางอีเมล์ที่ระบุไว้',
							// ],
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
								'placeholder' => 'ชื่อ นามสกุล',
							],
							'phone' => [
								'label' => 'โทรศัพท์มือถือ (Mobille Phone)',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
								'placeholder' => 'หมายเลขโทรศัพท์มือถือ',
							],
							'orgId' => ['type' => 'hidden', 'label' => 'มหาวิทยาลัย/สถาบันการศึกษา', 'require' => true],
							'orgName' => [
								'label' => 'มหาวิทยาลัย/สถาบันการศึกษา',
								'type' => 'text',
								'class' => 'sg-autocomplete -fill',
								'require' => true,
								'attr' => [
									'data-query' => url('org/api/org', ['parent' => 'none', 'sector' => 10, 'enShortName' => 'Yes']),
									'data-altfld' => 'edit-data-orgid',
								],
								'placeholder' => 'ชื่อมหาวิทยาลัย/สถาบันการศึกษา',
								// 'pretext' => '<div class="input-prepend"><span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#edit-data-orgname").val("");\'><i class="icon -material -gray -sg-16">clear</i></a></span></div>',
								// 'posttext' => '<div class="input-append"><span><a><i class="icon -material -gray">search</i></a></span></div>',
								// 'container' => '{class: "-group"}',
							],
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
					$this->_script(),
				], // children
			]), // Row
		]);
	}

	function _script() {
		return '<script type="text/javascript">
		$("#edit-data-reemail").blur(function() {
			let $this = $(this)
			$("#form-item-edit-data-reemail>.-error").remove()
			if ($this.val() != $("#edit-data-email").val()) {
				$("#form-item-edit-data-reemail>.description").after($("<span></span>").addClass("-error").text("อีเมล์ ไม่ตรงกับ ยืนยันอีเมล์ !!!!"))
			} else {

			}
		})
		</script>';
	}

	function _saveRequest() {
		$userConfig = cfg('project')->user;
		$data = (Object) post('data');

		if (empty($data->name))
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุชื่อ นามสกุล']);
		else if (empty($data->password))
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุรหัสผ่าน']);
		else if ($data->rePassword && $data->password != $data->rePassword)
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'รหัสผ่าน ไม่ตรงกับ ยืนยันรหัสผ่าน']);
		else if ($data->password && strlen($data->password) < 6)
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุรหัสผ่านอย่างน้อย 6 ตัวอักษร']);
		else if (empty($data->email))
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุอีเมล์ (E-mail)']);
		else if ($data->email && !sg_is_email($data->email))
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'อีเมล์ (E-mail) ไม่ถูกต้อง']);
		else if ($data->reEmail && $data->email != $data->reEmail)
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'อีเมล์ ไม่ตรงกับ ยืนยันอีเมล์']);
		else if ($data->email && UserModel::get(['email' => $data->email]))
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'อีเมล์ <strong><em>'.$data->email.'</em></strong> ได้มีการลงทะเบียนไว้แล้ว ไม่สามารถใช้ซ้ำได้']);
		else if (empty($data->phone))
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุหมายเลขโทรศัพท์']);
		else if (empty($data->orgName))
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'กรุณาระบุมหาวิทยาลัย/สถาบันการศึกษา']);

		unset($data->rePassword);

		// debugMsg($data,'$data');
		// debugMsg($userConfig, '$userConfig');
		// debugMsg($userConfig->register->method);
		$returnWidget = NULL;

		switch ($userConfig->register->method) {
			case 'WAIT_FOR_APPROVE':
				$result = BigData::Add(
					[
						'keyName' => 'user',
						'field' => 'request',
						'type' => 'JSON',
						'data' => $data,
					],
					'{debug: false}'
				);
				// debugMsg($result, '$result');
				$returnWidget = new Widget([
					'children' => [
						'<p class="notify -sg-paddingmore" style="margin: 32px 0;">สมัครใช้บริการเรียบร้อย กรุณารอการตรวจสอบจากผู้ดูแลระบบ</p>',
						new Container([
							'class' => 'sg-paddingmore -sg-text-center',
							'child' => '<a class="sg-action btn -primary" href="{url:/}" data-rel="close"><i class="icon -material">chevron_left</i><span>กลับสู่หน้าแรก</span></a>',
						]),
					], // children
				]);
				break;

			case 'ENABLE':
				$orgInfo = OrgModel::get($data->orgId);

				$userInfo = (Object) [
					'username' => UserModel::getNextUsername($orgInfo->info->enshortname,'-',4),
					'password' => $data->password,
					'name' => $data->name,
					'email' => $data->email,
					'phone' => $data->phone,
					'organization' => $data->orgName,
					'admin_remark' => 'Create from nxt/user/request',
				];

				$canCreate = $userInfo->username;
					// debugMsg($userInfo, '$userInfo');

				if ($canCreate) {
					$result = UserModel::create($userInfo);
					// debugMsg($result, '$result');
					if ($result->complete) {
						// BigData::updateKeyId(['autoId' => $requestInfo->id, 'keyId' => $result->uid]);
						mydb::query(
							'INSERT INTO %org_officer% (`orgId`, `uid`, `membership`) VALUES (:orgId, :uid, :membership)',
							[
								':orgId' => $data->orgId,
								':uid' => $result->uid,
								':membership' => 'MEMBER',
							]
						);
						// debugMsg(mydb()->_query);

						UserModel::signInProcess($userInfo->username,$userInfo->password);

						$returnWidget = new Widget([
							'children' => [
							'<p class="notify -sg-paddingmore" style="margin: 32px 0;">สมัครใช้บริการเรียบร้อย ท่านสามารถเข้าสู่ระบบสมาชิกเพื่อเริ่มใช้งานได้ทันที</p>',
							new Table([
								'caption' => 'ข้อมูลสำหรับเข้าสู่ระบบสมาชิก',
								'children' => [
									['Username', $userInfo->username],
									['E-mail', $userInfo->email],
									['Password', $userInfo->password],
								], // children
							]), // Table
							new Container([
								'class' => 'sg-paddingmore -sg-text-center',
								'child' => '<a class="btn -primary" href="'.url('project/nxt').'"><i class="icon -material">chevron_left</i><span>เข้าสู่หน้าเสนอหลักสูตร</span></a>',
							]),
							// '<a class="sg-action btn -primary" href="javascript:void(0)" data-rel="none" data-done="close">เรียบร้อย</a>',
								// new DebugMsg($result, '$result'),
							], // children
						]);
					} else {
						$returnWidget = new Widget([
							'children' => [
								message('error', 'ERROR!!! '.$result->error),
								// new DebugMsg($result, '$result'),
							],
						]);
					}
				}
				break;
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สมัครใช้บริการ',
				'boxHeader' => true,
				// 'leading' => _HEADER_BACK,
			]),
			'body' => $returnWidget,
		]);
	}
}
?>