<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_app_my_profile_info($self) {
	R::View('imed.toolbar', $self, 'Change Account Profile @'.i()->name, 'none');

	$ret = '';

	$uid = i()->uid;

	$ret = '<header class="header -box -hidden"><h3>Change Account Profile</h3></header>';

	$userInfo  = R::Model('user.get',$uid);


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
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('imed/app/menu',array('closewebview'=>'YES')).'" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a> ',
					)
				);

	$ret .= $form->build();
	return $ret;
}
?>