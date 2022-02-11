<?php
/**
* Project : Change Profile Form
* Created 2020-09-21
* Modify  2021-12-14
*
* @param String $arg1
* @return Widget
*
* @usage project/my/profile/info
*/

class ProjectMyProfileInfo extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
	$uid = i()->uid;
	$userInfo  = R::Model('user.get',$uid);

		// $ret = '<header class="header -box -hidden">'._HEADER_BACK.'<h3>Change Account Profile</h3></header>';
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Change Account Profile @'.i()->name,
			]), // AppBar
			'body' => 	new Form([
				'variable' => 'profile',
				'action' => url('my/api/profile.update'),
				'class' => 'sg-form',
				'checkValid' => true,
				'rel' => 'notify',
				'done' => 'close',
				'children' => [
					'name' => [
						'type' => 'text',
						'label' => 'ชื่อ-นามสกุล',
						'class' => '-fill',
						'maxlength' => 255,
						'require' => true,
						'value' => $userInfo->name,
						'description' => 'ชื่อ-นามสกุล เป็นชื่อสำหรับนำไปแสดงผลเมื่อท่านส่งข้อมูล หากเปลี่ยนชื่อ-นามสกุล กรุณาออกจากระบบสมาชิกและเข้าสู่ระบบสมาชิกอีกครั้ง',
					],
					'email' => [
						'type' => 'text',
						'label' => 'อีเมล์',
						'class' => '-fill',
						'maxlength' => 255,
						'require' => true,
						'value' => $userInfo->email,
						'description' => 'อีเมล์มีไว้เพื่อทำการติดต่อหรือการยืนยันข้อมูลระหว่างท่านกับเว็บไซท์ จะไม่มีการแสดงอีเมล์ของท่านในหน้าเว็บไซท์ให้ผู้เห็นโดยเด็ดขาด',
					],
					'organization' => [
						'type' => 'text',
						'label' => 'หน่วยงาน / องค์กร / บริษัท',
						'class' => '-fill',
						'maxlength' => 100,
						'value' => $userInfo->organization,
					],
					'position' => [
						'type' => 'text',
						'label' => 'ตำแหน่ง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $userInfo->position,
					],
					'mobile' => [
						'type' => 'text',
						'label' => 'โทรศัพท์',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => $userInfo->mobile,
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
						'container' => array('class' => '-sg-text-right'),
						//'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a> ',
					],
				], // children
			]), // Form
		]);
	}
}
?>
<?php
/**
* Project : Change Profile Form
* Created 2020-09-21
* Modify  2020-09-21
*
* @param Object $self
* @return String
*
* @usage project/my/profile/info
*/

$debug = true;

function project_my_profile_info($self) {
	new Toolbar($self, 'Change Account Profile @'.i()->name);

	$uid = i()->uid;
	$userInfo  = R::Model('user.get',$uid);

	$ret = '<header class="header -box -hidden">'._HEADER_BACK.'<h3>Change Account Profile</h3></header>';



	$form = new Form('profile', url('my/api/profile.update',array('closewebview'=>'YES')), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close');


	$form->addField('name',
		array(
			'type' => 'text',
			'label' => 'ชื่อ-นามสกุล',
			'class' => '-fill',
			'maxlength' => 255,
			'require' => true,
			'value' => htmlspecialchars($userInfo->name),
			'description' => 'ชื่อ-นามสกุล เป็นชื่อสำหรับนำไปแสดงผลเมื่อท่านส่งข้อมูล หากเปลี่ยนชื่อ-นามสกุล กรุณาออกจากระบบสมาชิกและเข้าสู่ระบบสมาชิกอีกครั้ง',
		)
	);

	$form->addField('email',
		array(
			'type' => 'text',
			'label' => 'อีเมล์',
			'class' => '-fill',
			'maxlength' => 255,
			'require' => true,
			'value' => htmlspecialchars($userInfo->email),
			'description' => 'อีเมล์มีไว้เพื่อทำการติดต่อหรือการยืนยันข้อมูลระหว่างท่านกับเว็บไซท์ จะไม่มีการแสดงอีเมล์ของท่านในหน้าเว็บไซท์ให้ผู้เห็นโดยเด็ดขาด',
		)
	);

	$form->addField('organization',
		array(
			'type' => 'text',
			'label' => 'หน่วยงาน / องค์กร / บริษัท',
			'class' => '-fill',
			'maxlength' => 100,
			'value' => htmlspecialchars($userInfo->organization),
		)
	);

	$form->addField('position',
		array(
			'type' => 'text',
			'label' => 'ตำแหน่ง',
			'class' => '-fill',
			'maxlength' => 50,
			'value' => htmlspecialchars($userInfo->position),
		)
	);

	$form->addField('mobile',
		array(
			'type' => 'text',
			'label' => 'โทรศัพท์',
			'class' => '-fill',
			'maxlength' => 20,
			'value' => htmlspecialchars($userInfo->mobile),
		)
	);

	$form->addField('save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
			'container' => array('class' => '-sg-text-right'),
			//'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a> ',
		)
	);

	$ret .= $form->build();
	return $ret;
}
?>