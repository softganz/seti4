<?php
function profile_detail($self, $uid = NULL) {
	if (empty($uid)) $uid = i()->uid;

	$user  = R::Model('user.get',$uid);

	if (!user_access('administer users','change own profile', $user->uid))
		return message('error','Access denied');

	R::View('profile.toolbar', $self, $user->uid);

	$ret = '<h3>Change user profile</h3>';

	if (post('cancel'))
		location('profile/'.$user->uid);

	$profile = (object) post('profile', _TRIM+_STRIPTAG);

	if ($profile->name) {
		if (empty($profile->name)) $error[]='กรุณาป้อนนามแฝง';
		if (empty($profile->email)) $error[]='กรุณาป้อนอีเมล์';
		else if (!sg_is_email($profile->email)) $error[]='กรุณาป้อนอีเมล์ให้ถูกต้องตามรูปแบบ คือ yourname@domain.com';
		else if ($profile->email && mydb::select('SELECT `uid` FROM %users% WHERE `email` = :email AND `uid` != :uid LIMIT 1',':email', $profile->email, ':uid', $user->uid)->uid ) $error[]='อีเมล์ <strong><em>'.$profile->email.'</em></strong> ได้มีการลงทะเบียนไว้แล้ว หรือ <a href="'.url('user/password/get').'">ท่านจำรหัสผ่านไม่ได้</a>'; //-- duplicate email
		if (sg_invalid_poster_name($profile->name)) $error[]='Duplicate name : มีผู้อื่นใช้ชื่อ <em>'.$profile->name.'</em> ไปแล้ว กรุณาเปลี่ยนเป็นชื่ออื่น';
		if (!$error) {
			$profile->uid = $user->uid;
			$stmt = mydb::create_update_cmd('%users%', $profile, '`uid` = :uid');
			mydb::query($stmt, $profile);
			location('profile/'.$user->uid);
		}
	} else {
		$profile=(object)$user;
	}

	$self->title=sg_client_convert('แก้ไขรายละเอียดสมาชิก');

	if ($error) $ret.=$message=message('error',$error);

	$form = new Form('profile', url(q()));

	$form->addField('save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
						'container' => array('class' => '-sg-text-right'),
						'pretext' => '<a href="">CANCEL</a> ',
					)
				);


	$form->addField('name',
					array(
						'type' => 'text',
						'label' => 'นามแฝง',
						'class' => '-fill',
						'maxlength' => 255,
						'require' => true,
						'value' => htmlspecialchars($profile->name),
						'description' => 'นามแฝงเป็นชื่อสำหรับนำไปแสดงผลเมื่อท่านส่งข้อมูล',
					)
				);

	$form->addField('email',
					array(
						'type' => 'text',
						'label' => 'อีเมล์',
						'class' => '-fill',
						'maxlength' => 255,
						'require' => true,
						'value' => htmlspecialchars($profile->email),
						'description' => 'อีเมล์มีไว้เพื่อทำการติดต่อหรือการยืนยันข้อมูลระหว่างท่านกับเว็บไซท์ จะไม่มีการแสดงอีเมล์ของท่านในหน้าเว็บไซท์ให้ผู้เห็นโดยเด็ดขาด',
					)
				);

	$form->addText('<div class="help">ข้อมูลที่ท่านกรอกด้านล่างต่อไปนี้ จะนำมาแสดงให้สมาชิกดูในเว็บไซท์ หากมีข้อมูลใด ๆ เป็นข้อมูลส่วนตัวที่ท่านไม่ต้องการให้ผู้อื่นรู้ ท่านไม่จำเป็นต้องป้อนข้อมูลในช่องดังกล่าว</div>');

	$form->addField('name_prefix',
					array(
						'type' => 'text',
						'label' => 'คำนำหน้านาม',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => htmlspecialchars($profile->name_prefix),
					)
				);

	$form->addField('real_name',
					array(
						'type' => 'text',
						'label' => 'ชื่อจริง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => htmlspecialchars($profile->real_name),
					)
				);

	$form->addField('mid_name',
					array(
						'type' => 'text',
						'label' => 'ชื่อกลาง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => htmlspecialchars($profile->mid_name),
					)
				);

	$form->addField('last_name',
					array(
						'type' => 'text',
						'label' => 'นามสกุล',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => htmlspecialchars($profile->last_name),
					)
				);

	$form->addField('occupation',
					array(
						'type' => 'text',
						'label' => 'อาชีพ',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => htmlspecialchars($profile->occupation),
					)
				);

	$form->addField('position',
					array(
						'type' => 'text',
						'label' => 'ตำแหน่ง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => htmlspecialchars($profile->position),
					)
				);

	$form->addField('organization',
					array(
						'type' => 'text',
						'label' => 'องค์กร / บริษัท',
						'class' => '-fill',
						'maxlength' => 100,
						'value' => htmlspecialchars($profile->organization),
					)
				);

	$form->addField('address',
					array(
						'type' => 'text',
						'label' => 'ที่อยู่',
						'class' => '-fill',
						'maxlength' => 255,
						'value' => htmlspecialchars($profile->address),
					)
				);

	$form->addField('amphur',
					array(
						'type' => 'text',
						'label' => 'อำเภอ',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => htmlspecialchars($profile->amphur),
					)
				);

	$form->addField('province',
					array(
						'type' => 'text',
						'label' => 'จังหวัด',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => htmlspecialchars($profile->province),
					)
				);

	$form->addField('zipcode',
					array(
						'type' => 'text',
						'label' => 'รหัสไปรษณีย์',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => htmlspecialchars($profile->zipcode),
					)
				);

	$form->addField('country',
					array(
						'type' => 'text',
						'label' => 'ประเทศ',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => htmlspecialchars($profile->country),
					)
				);

	$form->addField('latitude',
					array(
						'type' => 'text',
						'label' => 'ละติจูด',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => htmlspecialchars($profile->latitude),
					)
				);

	$form->addField('longitude',
					array(
						'type' => 'text',
						'label' => 'ลองกิจูด',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => htmlspecialchars($profile->longitude),
					)
				);

	$form->addField('phone',
					array(
						'type' => 'text',
						'label' => 'โทรศัพท์',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => htmlspecialchars($profile->phone),
					)
				);

	$form->addField('mobile',
					array(
						'type' => 'text',
						'label' => 'โทรศัพท์เคลื่อนที่',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => htmlspecialchars($profile->mobile),
					)
				);

	$form->addField('fax',
					array(
						'type' => 'text',
						'label' => 'โทรสาร',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => htmlspecialchars($profile->fax),
					)
				);

	$form->addField('website',
					array(
						'type' => 'text',
						'label' => 'เว็บไซต์',
						'class' => '-fill',
						'maxlength' => 200,
						'value' => htmlspecialchars($profile->website),
					)
				);

	$form->addField('about',
					array(
						'type' => 'textarea',
						'label' => 'ประวัติย่อ',
						'class' => '-fill',
						'rows' => 12,
						'value' => $profile->about,
					)
				);

	$form->addField('save2',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
						'container' => array('class' => '-sg-text-right'),
						'pretext' => '<a href="'.url('profile/'.$user->uid).'">CANCEL</a> ',
					)
				);

	$ret .= $form->build();

	return $ret;
}
?>