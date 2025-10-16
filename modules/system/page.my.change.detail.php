<?php
/**
* My      :: Change My Detail Information
* Created :: 20xx-xx-xx
* Modify  :: 2025-06-25
* Version :: 2
*
* @param String $args
* @return Widget
*
* @usage module/{Id}/method
*/

class MyChangeDetail extends Page {
	var $args;

	function __construct() {
		parent::__construct([
		]);
	}

	function build() {
		$uid = i()->uid;

		$userInfo  = R::Model('user.get',$uid);

		if (post('cancel')) location('profile/'.$userInfo->uid);

		$profile = (object) post('profile', _TRIM+_STRIPTAG);

		if ($profile->name) {
			if (empty($profile->name)) $error[]='กรุณาป้อนนามแฝง';
			if (empty($profile->email)) $error[]='กรุณาป้อนอีเมล์';
			else if (!sg_is_email($profile->email)) $error[]='กรุณาป้อนอีเมล์ให้ถูกต้องตามรูปแบบ คือ yourname@domain.com';
			else if ($profile->email && mydb::select('SELECT `uid` FROM %users% WHERE `email` = :email AND `uid` != :uid LIMIT 1',':email', $profile->email, ':uid', $userInfo->uid)->uid ) $error[]='อีเมล์ <strong><em>'.$profile->email.'</em></strong> ได้มีการลงทะเบียนไว้แล้ว หรือ <a href="'.url('user/password').'">ท่านจำรหัสผ่านไม่ได้</a>'; //-- duplicate email
			if (sg_invalid_poster_name($profile->name)) $error[]='Duplicate name : มีผู้อื่นใช้ชื่อ <em>'.$profile->name.'</em> ไปแล้ว กรุณาเปลี่ยนเป็นชื่ออื่น';

			if ($error) {
				header('HTTP/1.0 406 Not Acceptable');
			} else {
				$profile->uid = $userInfo->uid;
				$stmt = mydb::create_update_cmd('%users%', $profile, '`uid` = :uid');
				mydb::query($stmt, $profile);
				if (post('ret')) {
					location(post('ret'));
				} else {
					$ret .= 'บันทึกเรียบร้อย';
				}
				return $ret;
			}
		} else {
			$profile=(object)$userInfo;
		}

		if ($error) $ret.=$message=message('error',$error);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Change Account Profile @'.i()->name,
			]), // AppBar
			'sideBar' => new SideBar([
				'style' => 'width: 200px;',
				'child' => R::View('my.menu'),
			]),
			'body' => new Form([
				'variable' => 'profile',
				'action' => url('my/change/detail', ['closewebview' => 'YES']),
				'class' => 'sg-form',
				'checkValid' => true,
				'rel' => _AJAX ? 'box' : NULL,
				'done' => _AJAX ? 'close | load' : NULL,
				'children' => [
					'ret' => !_AJAX ? ['type' => 'hidden', 'name' => 'ret', 'value' => 'my'] : NULL,
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'container' => array('class' => '-sg-text-right'),
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('my').'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a> ',
					],
					'name' =>[
						'type' => 'text',
						'label' => 'นามแฝง',
						'class' => '-fill',
						'maxlength' => 255,
						'require' => true,
						'value' => $profile->name,
						'description' => 'นามแฝงเป็นชื่อสำหรับนำไปแสดงผลเมื่อท่านส่งข้อมูล หากเปลี่ยนนามแฝง กรุณาออกจากระบบสมาชิกและเข้าสู่ระบบสมาชิกอีกครั้ง',
					],
					'email' => [
						'type' => 'text',
						'label' => 'อีเมล์',
						'class' => '-fill',
						'maxlength' => 255,
						'require' => true,
						'value' => $profile->email,
						'description' => 'อีเมล์มีไว้เพื่อทำการติดต่อหรือการยืนยันข้อมูลระหว่างท่านกับเว็บไซท์ จะไม่มีการแสดงอีเมล์ของท่านในหน้าเว็บไซท์ให้ผู้เห็นโดยเด็ดขาด',
					],
					'<div class="notify"><b>คำเตือน : ข้อมูลที่ท่านกรอกด้านล่างต่อไปนี้ จะนำมาแสดงให้สมาชิกดูในเว็บไซท์ หากมีข้อมูลใด ๆ เป็นข้อมูลส่วนตัวที่ท่านไม่ต้องการให้ผู้อื่นรู้ ท่านไม่จำเป็นต้องป้อนข้อมูลในช่องดังกล่าว</b></div>',
					'name_prefix' => [
						'type' => 'text',
						'label' => 'คำนำหน้านาม',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => $profile->name_prefix,
					],
					'real_name' => [
						'type' => 'text',
						'label' => 'ชื่อจริง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $profile->real_name,
					],
					'mid_name' => [
						'type' => 'text',
						'label' => 'ชื่อกลาง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $profile->mid_name,
					],
					'last_name' => [
						'type' => 'text',
						'label' => 'นามสกุล',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $profile->last_name,
					],
					'occupation' => [
						'type' => 'text',
						'label' => 'อาชีพ',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $profile->occupation,
					],
					'position' => [
						'type' => 'text',
						'label' => 'ตำแหน่ง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $profile->position,
					],
					'organization' => [
						'type' => 'text',
						'label' => 'องค์กร / บริษัท',
						'class' => '-fill',
						'maxlength' => 100,
						'value' => $profile->organization,
					],
					'address' => [
						'type' => 'text',
						'label' => 'ที่อยู่',
						'class' => '-fill',
						'maxlength' => 255,
						'value' => $profile->address,
					],
					'amphur' => [
						'type' => 'text',
						'label' => 'อำเภอ',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $profile->amphur,
					],
					'province' => [
						'type' => 'text',
						'label' => 'จังหวัด',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $profile->province,
					],
					'zipcode' => [
						'type' => 'text',
						'label' => 'รหัสไปรษณีย์',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => $profile->zipcode,
					],
					'country' => [
						'type' => 'text',
						'label' => 'ประเทศ',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $profile->country,
					],
					'latitude' => [
						'type' => 'text',
						'label' => 'ละติจูด',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => $profile->latitude,
					],
					'longitude' => [
						'type' => 'text',
						'label' => 'ลองกิจูด',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => $profile->longitude,
					],
					'phone' => [
						'type' => 'text',
						'label' => 'โทรศัพท์',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => $profile->phone,
					],
					'mobile' => [
						'type' => 'text',
						'label' => 'โทรศัพท์เคลื่อนที่',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => $profile->mobile,
					],
					'fax' => [
						'type' => 'text',
						'label' => 'โทรสาร',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => $profile->fax,
					],
					'website' => [
						'type' => 'text',
						'label' => 'เว็บไซต์',
						'class' => '-fill',
						'maxlength' => 200,
						'value' => $profile->website,
					],
					'about' => [
						'type' => 'textarea',
						'label' => 'ประวัติย่อ',
						'class' => '-fill',
						'rows' => 12,
						'value' => $profile->about,
					],
					'save2' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'container' => ['class' => '-sg-text-right'],
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('my').'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a> ',
					],
				], // children
			]), // Form
		]);
	}
}
?>