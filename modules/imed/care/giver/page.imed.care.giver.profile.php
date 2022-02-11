<?php
/**
* iMed :: Care Giver Profile
* Created 2021-12-22
* Modify  2022-01-08
*
* @return Widget
*
* @usage imed/care/giver/{id}/profile
*/

import('widget:profile.photo.php');

class ImedCareGiverProfile extends Page {
	var $userId;
	var $right;
	var $userInfo;

	function __construct($userInfo = NULL) {
		$this->userId = $userInfo->userId;
		$this->userInfo = $userInfo;
		$this->right = (Object) [
			// 'edit' => (i()->ok && i()->uid == $userInfo->userId) || is_admin('imed'),
			'edit' => i()->ok && i()->uid == $userInfo->userId,
		];
	}

	function build() {
		if (!$this->userInfo->userId) return 'PROCESS ERROR';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->userInfo->name,
				'leading' => '<i class="icon -material">person</i>',
				'removeOnApp' => true,
			]),
			'body' => new Container([
				'class' => 'imed-care-giver-profile'.($this->right->edit ? ' sg-inline-edit' : ''),
				'attribute' => $this->right->edit ? [
					'data-update-url' => url('imed/my/api/update.info'),
					'data-debug' => debug('inline') ? 'inline' : NULL,
				] : NULL,
				'children' => [
					new ProfilePhotoWidget([
						'photo' => model::user_photo($this->userInfo->username),
						'children' => [
							$this->right->edit ? new Form([
								'class' => 'sg-form -upload -upload-profile-photo',
								'enctype' => 'multipart/form-data',
								'action' => url('my/api/photo.change'),
								'rel' => 'notify',
								'done' => 'load',
								'children' => [
									'<span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" onchange="$(this).closest(form).submit(); return false;"></span>'
									], // children
							]) : NULL, // Form
							// 'ชื่อที่ใช้ในการเข้าระบบ'.' : <strong><a href="'.url('admin/user/edit/'.i()->uid).'">'.i()->username.'</a></strong>',
						],
					]), // ProfilePhotoWidget

					// ข้อมูลส่วนบุคคล
					new Card([
						'children' => [
							new ListTile([
								'title' => 'ข้อมูลส่วนบุคคล',
								'leading' => '<i class="icon -material">person</i>',
							]), // ListTile
							new Container([
								'class' => '-sg-paddingnorm',
								'children' => [
									view::inlineedit(
										[
											'label' => 'ชื่อสำหรับแสดง',
											'group' => 'profile',
											'fld' => 'name',
											'value' => $this->userInfo->name,
											'options' => '{class: "-fill"}',
										],
										$this->userInfo->name,
										$this->right->edit
									),
									view::inlineedit(
										[
											'label' => 'ชื่อ นามสกุล',
											'group' => 'profile',
											'fld' => 'fullName',
											'value' => $this->userInfo->fullName,
											'options' => '{class: "-fill"}',
										],
										$this->userInfo->fullName,
										$this->right->edit
									),
									view::inlineedit(
										[
										 'label' => 'ที่อยู่สำหรับการติดต่อ',
											'group' => 'profile',
											'fld' => 'address',
											'value' => $this->userInfo->address,
											'options' => '{class: "-fill"}',
										],
										$this->userInfo->address,
										$this->right->edit
									),
									view::inlineedit(
										[
										'label' => 'โทรศัพท์มือถือ',
											'group' => 'profile',
											'fld' => 'phone',
											'value' => $this->userInfo->phone,
											'options' => '{class: "-fill"}',
										],
										$this->userInfo->phone,
										$this->right->edit
									),
									view::inlineedit(
										[
											'label' => 'ID Line',
											'group' => 'bigdataJson:user/profile/'.$this->userId,
											'fld' => 'lineId',
											'value' => $this->userInfo->lineId,
											'options' => '{class: "-fill"}',
										],
										$this->userInfo->lineId,
										$this->right->edit
									),
								], // children
							]), // Container

							// new Table([
							// 	'children' => [
							// 		[
							// 			'ชื่อสำหรับแสดง',
							// 			view::inlineedit(
							// 				[
							// 					'group' => 'profile',
							// 					'fld' => 'name',
							// 					'value' => $this->userInfo->name,
							// 					'options' => '{class: "-fill"}',
							// 				],
							// 				$this->userInfo->name,
							// 				$this->right->edit
							// 			)
							// 		],
							// 		[
							// 			'ชื่อ นามสกุล',
							// 			// 'นาย/นาง/นางสาว'
							// 			view::inlineedit(
							// 				[
							// 					'group' => 'profile',
							// 					'fld' => 'fullName',
							// 					'value' => $this->userInfo->fullName,
							// 					'options' => '{class: "-fill"}',
							// 				],
							// 				$this->userInfo->fullName,
							// 				$this->right->edit
							// 			)
							// 		],
							// 		[
							// 			'ที่อยู่สำหรับการติดต่อ',
							// 			view::inlineedit(
							// 				[
							// 					'group' => 'profile',
							// 					'fld' => 'address',
							// 					'value' => $this->userInfo->address,
							// 					'options' => '{class: "-fill"}',
							// 				],
							// 				$this->userInfo->address,
							// 				$this->right->edit
							// 			)
							// 		],
							// 		[
							// 			'โทรศัพท์มือถือ',
							// 			view::inlineedit(
							// 				[
							// 					'group' => 'profile',
							// 					'fld' => 'phone',
							// 					'value' => $this->userInfo->phone,
							// 					'options' => '{class: "-fill"}',
							// 				],
							// 				$this->userInfo->phone,
							// 				$this->right->edit
							// 			)
							// 		],
							// 		[
							// 			'ID Line',
							// 			view::inlineedit(
							// 				[
							// 					'group' => 'bigdataJson:user/profile/'.$this->userId,
							// 					'fld' => 'lineId',
							// 					'value' => $this->userInfo->lineId,
							// 					'options' => '{class: "-fill"}',
							// 				],
							// 				$this->userInfo->lineId,
							// 				$this->right->edit
							// 			)
							// 		],
							// 	], // children
							// ]), // Table
						], // children
					]), // Card
					// new DebugMsg($this->userInfo,'$this->userInfo'),
					// '<i class="icon -material -sg-48 -gray">toggle_off</i>'

					// พื้นที่ดำเนินการ
					new Card([
						'children' => [
							new ListTile([
								'title' => 'พื้นที่ดำเนินการ',
								'leading' => '<i class="icon -material">star</i>',
							]),
						], // children
					]),

					// ภาพถ่ายบัตรประชาชน
					new Card([
						'children' => [
							new ListTile([
								'title' => 'ภาพถ่ายบัตรประชาชน',
								'leading' => '<i class="icon -material">star</i>',
							]),
						], // children
					]),

					// ภาพถ่ายตัวจริงหน้าตรง
					new Card([
						'children' => [
							new ListTile([
								'title' => 'ภาพถ่ายตัวจริงหน้าตรง',
								'leading' => '<i class="icon -material">star</i>',
							]),
						], // children
					]),

					// Serviceability
					new Card([
						'children' => [
							new ListTile([
								'title' => 'ความสามารถในการดูแลผู้ป่วยที่บ้าน',
								'leading' => '<i class="icon -material">checklist</i>',
							]),

							new Container([
								'class' => '-sg-text-center',
								'child' => '(คะแนนความสามารถ 1-5 คะแนนยิ่งมาก ความสามารถยิ่งสูง, 1 = ต่ำที่สุด 5 = สูงที่สุด)',
							]), // Container
							new Widget([
								'children' => array_map(
									function($item) {
										static $no = 0;
										return new Card([
											'children' => [
												new ListTile([
													'crossAxisAlignment' => 'top',
													'title' => $item->title,
													'leading' => '<i class="icon -sg-48"><img src="'.url($item->icon).'" /></i>',
													'subtitle' => $item->detail,
													// 'leading' => ++$no.'.',
												]),
												new Table([
													'class' => '-serv -center',
													'colgroup' => ['', '-center', '-center', '-center', '-center', '-center'],
													'thead' => '<!-- <tr><th colspan="5">คะแนนความสามารถ</th></tr>--><tr><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th></tr>',
													'children' => [
														array_map(
															function($scoreIndex) use ($item) {
																$icon = '<i class="icon -material -sg-32 '.($item->score == $scoreIndex ? '-green' : '-gray').'">check_circle</i>';
																return [
																	$this->right->edit ? '<a class="sg-action btn -link" href="'.url('imed/my/api/servable.save/'.$item->servId, ['score' => $scoreIndex]).'" data-rel="notify" data-done="load">'.$icon.'</a>' : $icon
																];
															},
															[1,2,3,4,5]
														)
													], // children
												]), // Table
												// new DebugMsg($item, '$item'),
											], // children
										]);
									},
									mydb::select(
										'SELECT a.`uid`, s.`servId`, a.`score`
										, s.`name`, s.`title`, s.`icon`, s.`detail`
										FROM %imed_code_serv% s
											LEFT JOIN %imed_servable% a ON a.`servId` = s.`servId` AND a.`uid` = :userId
										WHERE s.`package` IS NULL',
										[':userId' => $this->userId]
									)->items
									// [
									// 	'การทำความสะอาดร่างกาย (ลำตัว แขนขา หู ตา ปากฟัน ระบบขับถ่าย สระผม โกนหนวด ตัดเล็บ นวดหลัง) และเปลี่ยนเสื้อผ้า',
									// 	'การทำความสะอาดสภาพแวดล้อม (ที่นั่ง/ที่นอน เปลี่ยนผ้าปู เบาะ หมอน เครื่องนอน)',
									// 	'การให้อาหารทางสายยาง',
									// 	'การป้อนอาหารทางปาก',
									// 	'การพลิกตะแคงตัว',
									// 	'การบริหารร่างกาย ยืดเหยียดข้อและกล้ามเนื้อ',
									// 	'การเคาะปอดและดูดเสมหะ',
									// 	'การทำแผล (แผลเจาะคอ แผกดทับ แผลเรื้อรัง)',
									// 	'การพาขึ้นลงจากเตียง/นั่งรถเข็น',
									// 	'การพูดคุยเป็นเพื่อน/ช่วยเหลือทั่วไป',
									// ]
								), // children
								// new debugMsg(mydb()->_query),
							]), // Widget
						], // children
					]), // Card

					// ประสบการณ์การทำงานที่เกี่ยวข้องกับการบริการดูแลผู้ป่วย
					new Card([
						'children' => [
							new ListTile([
								'crossAxisAlignment' => 'top',
								'title' => 'ประสบการณ์การทำงานที่เกี่ยวข้องกับการบริการดูแลผู้ป่วย',
								'leading' => '<i class="icon -material">star</i>',
							]),
							new Table([
								'children' => array_map(
									function($item) {
										$icon = '<i class="icon -material '.($this->userInfo->{$item} ? '-green' : '-gray').' -sg-32">check_circle</i>';
										return [$item, $this->right->edit ? '<a class="sg-action btn -link" href="'.url('imed/my/api/update.info', ['group' => 'bigdataJson:user/profile/'.$this->userId, 'fld' => $item, 'value' => $this->userInfo->{$item} == 'YES' ? '' : 'YES']).'" data-rel="notify" data-done="load" style="padding: 0;">'.$icon.'</a>' : $icon];
									},
									[
										'อสม.',
										'ผู้ดูแลท้องถิ่น (CG)',
										'นักบริบาลท้องถิ่น'
									]
								), // children
							]), // Table
							new Container([
								'class' => '-sg-paddingnorm',
								'children' => [
									view::inlineedit(
										[
											'label' => 'ระยะเวลาการดำเนินงาน (ปี)',
											'group' => 'bigdataJson:user/profile/'.$this->userId,
											'fld' => 'yearTakeCarePatient',
											'value' => $this->userInfo->yearTakeCarePatient,
											'options' => '{class: "-fill"}',
										],
										$this->userInfo->yearTakeCarePatient,
										$this->right->edit,
										'select',
										range(0,40)
									),
									view::inlineedit(
										[
											'label' => 'ประวัติการทำงานที่เกี่ยวข้องกับการดูแลผู้ป่วย',
											'group' => 'bigdata:user/profile.takeCarePatientHistory/'.$this->userId,
											'fld' => 'takeCarePatientHistory',
											'return-type' => 'html',
											'value' => $this->userInfo->takeCarePatientHistory,
											'options' => '{class: "-fill", placeholder: "บรรยายประวัติการทำงานที่เกี่ยวข้องกับการดูแลผู้ป่วย"}',
										],
										nl2br($this->userInfo->takeCarePatientHistory),
										$this->right->edit,
										'textarea'
									),
								], // children
							]), // Container
						], // children
					]), // Card

					// จำนวนผู้ป่วยที่เคยให้การดูแลที่บ้าน
					new Card([
						'children' => [
							new ListTile([
								'crossAxisAlignment' => 'top',
								'title' => 'จำนวนผู้ป่วยที่เคยให้การดูแลที่บ้าน (โดยประมาณ)',
								'leading' => '<i class="icon -material">star</i>',
							]),
							new Container([
								'class' => '-sg-paddingnorm',
								'children' => [
									view::inlineedit(
										[
											'label' => '1. ติดเตียง (ราย)',
											'group' => 'bigdataJson:user/profile/'.$this->userId,
											'fld' => 'bedriddenPatient',
											'value' => $this->userInfo->bedriddenPatient,
											'options' => '{class: "-fill"}',
										],
										$this->userInfo->bedriddenPatient,
										$this->right->edit,
										'select',
										range(0,100)
									),
									view::inlineedit(
										[
											'label' => '2. ติดบ้าน (ราย)',
											'group' => 'bigdataJson:user/profile/'.$this->userId,
											'fld' => 'stayAtHomePatient',
											'value' => $this->userInfo->stayAtHomePatient,
											'options' => '{class: "-fill"}',
										],
										$this->userInfo->stayAtHomePatient,
										$this->right->edit,
										'select',
										range(0,100)
									),
								], // children
							]), // Container
						], // children
					]), // Card

					// จำนวนผู้ป่วยที่ดูแลในระบบ
					new Card([
						'children' => [
							new ListTile([
								'title' => 'จำนวนผู้ป่วยที่ดูแลในระบบ',
								'leading' => '<i class="icon -material">star</i>',
							]),
						], // children
					]), // Card

					// new DebugMsg($this->userInfo, '$userInfo'),
					$this->_script(),
				], // children
			]), // Container
		]); // Scaffold
	}

	function _script() {
		return '<style type="text/css">
		.widget-table.-serv th {white-space: nowrap; vertical-align: top;}
		.imed-care-giver-profile>.widget-card {margin-bottom: 32px;}
		.widget-card>.widget-card {background-color: #fff; opacity: 0.8;}
		.sg-inline-edit label {font-weight: bold;}
		.sg-inline-edit .inline-edit-item {margin-bottom: 16px; display: block;}
		</style>';
	}
}
?>