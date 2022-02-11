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

class ProjectNxtUser extends Page {
	var $createId;

	function __construct() {
		parent::__construct();
		$this->createId = post('createId');
	}

	function build() {
		$isAdmin = is_admin();

		if (!$isAdmin) return message('error', 'Access Denied');

		if ($this->createId) return $this->_createUser();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ผู้สมัครใช้บริการ',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					new ScrollView([
						'child' => new Table([
							'thead' => ['approve -center' => '', 'ชื่อ-นามสกุล', 'มหาวิทยาลัย', 'โทรศัพท์', 'อีเมล์', 'date -date' => 'วันที่สมัคร', 'icons -center' => ''],
							'children' => (function() {
								$rows = [];
								foreach (BigData::items(['keyName' => 'user', 'field' => 'request'])->items as $item) {
									$item->data = SG\json_decode($item->data);
									$rows[] = [
										$item->keyId ? '<i class="icon -material -green">verified</i>' : '',
										$item->data->name,
										$item->data->orgName,
										$item->data->phone,
										$item->data->email,
										sg_date($item->created, 'ว ดด ปปปป H:i'),
										!$item->keyId ? '<a class="sg-action btn -link" href="'.url('project/nxt/user', ['createId' => $item->autoId]).'" data-rel="box" data-width="480"><i class="icon -material">add_circle</i></a>' : NULL,
									];
								}
								// debugMsg($result, '$result');
								// debugMsg(mydb()->_query);
								return $rows;
							})(), // children
							]), // Table
					]), // ScrollView
				], // children
			]), // Row
		]);
	}

	function _createUser() {
		$requestInfo = BigData::get($this->createId);
		$userByEmail = UserModel::get(['email' => $requestInfo->data->email], '{debug: false}');
		$orgInfo = OrgModel::get($requestInfo->data->orgId);

		$userInfo = (Object) [
			'username' => UserModel::getNextUsername($orgInfo->info->shortname,'-',4),
			'password' => sg_rand_password(),
			'name' => $requestInfo->data->name,
			'email' => $requestInfo->data->email,
			'phone' => $requestInfo->data->phone,
			'organization' => $requestInfo->data->orgName,
			'admin_remark' => 'Create by admin from user request',
		];

		$canCreate = !$userByEmail && $userInfo->username;

		if ($canCreate && SG\confirm()) {
			$result = UserModel::create($userInfo);
			if ($result->complete) {
				BigData::updateKeyId(['autoId' => $requestInfo->id, 'keyId' => $result->uid]);

				return new Widget([
					'children' => [
						'<a class="sg-action btn -primary" href="javascript:void(0)" data-rel="none" data-done="load | close">เรียบร้อย</a>',
						// new DebugMsg($result, '$result'),
					], // children
				]);
			} else {
				return new Widget([
					'children' => [
						message('error', 'ERROR!!! '.$result->error),
						// new DebugMsg($result, '$result'),
					],
				]);
			}
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Create user',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Container([
				'id' => 'info',
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'ข้อมูลสำหรับเข้าสู่ระบบสมาชิก',
								'leading' => '<i class="icon -material">key</i>',
							]),
							new Table([
								'thead' => ['label -nowrap' => '','value -fill' => ''],
								'showHeader' => false,
								'children' => [
									['e-mail', '<b>'.$userInfo->email.'</b>'],
									['username', '<b>'.$userInfo->username.'</b>'],
									['password', '<b>'.$userInfo->password.'</b>'],
								], // children
							]), // Table
						], // children
					]), // Card

					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'ข้อมูลส่วนบุคคล',
								'leading' => '<i class="icon -material">account_circle</i>',
							]),
							new Table([
								'thead' => ['label -nowrap' => '','value -fill' => ''],
								'showHeader' => false,
								'children' => [
									['ชื่อ-นามสกุล', $userInfo->name],
									['โทรศัพท์', $userInfo->phone],
									['มหาวิทยาลัย', $userInfo->organization],
								], // children
							]), // Table
						], // children
					]), // Card

					new Nav([
						'mainAxisAlignment' => 'end',
						'class' => '-sg-paddingnorm',
						'child' => $canCreate ? '<a class="sg-action btn -primary" href="'.url('project/nxt/user', ['createId' => $this->createId, 'confirm' => 'Yes']).'" data-rel="#info"><i class="icon -material">done_all</i><span>สร้างสมาชิก</span></a>' : '<a class="btn" href=""><i class="icon -material">edit</i><span>แก้ไขข้อมูล</span></a>',
					]), // Nav
					// new DebugMsg($requestInfo, '$requestInfo'),
					// new DebugMsg($userByEmail, '$userByEmail'),
				], // children
			]), // Widget
		]);
	}
}
?>