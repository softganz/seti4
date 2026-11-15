<?php
/**
 * My       :: Change My Detail Information
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 20xx-xx-xx
 * Modified :: 2026-11-15
 * Version  :: 4
 *
 * @uses module/{Id}/method
 */

namespace My;

use My\Widgets\MySidebarWidget;

class MyChangeDetail extends \Page {
	protected ?string $cancel;

	function __construct() {
		parent::__construct([
			'cancel' => \Request::all('cancel')
		]);
	}

	/**
	 * Right to build
	 *
	 * @return object|true
	 */
	function rightToBuild(): object|true {
		if (!i()->ok) {
			return new \SignForm();
		}
		return true;
	}

	/**
	 * Build page
	 *
	 * @return object
	 */
	#[\Override]
	function build(): object {
		if ($this->cancel) {
			location('my');
		}

		$userInfo = \UserModel::get(i()->uid);

		return new \Scaffold(
			appBar: new \AppBar(title: 'Change Account Profile @' . $userInfo->name), // AppBar
			sideBar: new MySidebarWidget(),
			body: new \Form(
				variable: 'profile',
				action: \Url::link('api/my/detail.save', ['closewebview' => 'YES']),
				class: 'sg-form',
				checkValid: true,
				rel: _AJAX ? 'box' : 'none',
				done: _AJAX ? 'close | load' : 'load:#main:' . \Url::link('my'),
				children: [
					'ret' => !_AJAX
						? ['type' => 'hidden', 'name' => 'ret', 'value' => 'my']
						: null,
					'save' => [
						'type' => 'button',
						'value' =>
							'<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'container' => ['class' => '-sg-text-right'],
						'pretext' =>
							'<a class="sg-action btn -link -cancel" href="'
							. \Url::link('my')
							. '" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a> '
					],
					'name' => [
						'type' => 'text',
						'label' => 'นามแฝง',
						'class' => '-fill',
						'maxlength' => 255,
						'require' => true,
						'value' => $userInfo->name,
						'description' =>
							'นามแฝงเป็นชื่อสำหรับนำไปแสดงผลเมื่อท่านส่งข้อมูล หากเปลี่ยนนามแฝง กรุณาออกจากระบบสมาชิกและเข้าสู่ระบบสมาชิกอีกครั้ง'
					],
					'email' => [
						'type' => 'text',
						'label' => 'อีเมล์',
						'class' => '-fill',
						'maxlength' => 255,
						'require' => true,
						'value' => $userInfo->email,
						'description' =>
							'อีเมล์มีไว้เพื่อทำการติดต่อหรือการยืนยันข้อมูลระหว่างท่านกับเว็บไซท์ จะไม่มีการแสดงอีเมล์ของท่านในหน้าเว็บไซท์ให้ผู้เห็นโดยเด็ดขาด'
					],
					new \Notify(
						child: '<b>คำเตือน : ข้อมูลที่ท่านกรอกด้านล่างต่อไปนี้ จะนำมาแสดงให้สมาชิกดูในเว็บไซท์ หากมีข้อมูลใด ๆ เป็นข้อมูลส่วนตัวที่ท่านไม่ต้องการให้ผู้อื่นรู้ ท่านไม่จำเป็นต้องป้อนข้อมูลในช่องดังกล่าว</b>'
					),
					'name_prefix' => [
						'type' => 'text',
						'label' => 'คำนำหน้านาม',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => $userInfo->name_prefix
					],
					'real_name' => [
						'type' => 'text',
						'label' => 'ชื่อจริง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $userInfo->real_name
					],
					'mid_name' => [
						'type' => 'text',
						'label' => 'ชื่อกลาง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $userInfo->mid_name
					],
					'last_name' => [
						'type' => 'text',
						'label' => 'นามสกุล',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $userInfo->last_name
					],
					'occupation' => [
						'type' => 'text',
						'label' => 'อาชีพ',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $userInfo->occupation
					],
					'position' => [
						'type' => 'text',
						'label' => 'ตำแหน่ง',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $userInfo->position
					],
					'organization' => [
						'type' => 'text',
						'label' => 'องค์กร / บริษัท',
						'class' => '-fill',
						'maxlength' => 100,
						'value' => $userInfo->organization
					],
					'address' => [
						'type' => 'text',
						'label' => 'ที่อยู่',
						'class' => '-fill',
						'maxlength' => 255,
						'value' => $userInfo->address
					],
					'amphur' => [
						'type' => 'text',
						'label' => 'อำเภอ',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $userInfo->amphur
					],
					'province' => [
						'type' => 'text',
						'label' => 'จังหวัด',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $userInfo->province
					],
					'zipcode' => [
						'type' => 'text',
						'label' => 'รหัสไปรษณีย์',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => $userInfo->zipcode
					],
					'country' => [
						'type' => 'text',
						'label' => 'ประเทศ',
						'class' => '-fill',
						'maxlength' => 50,
						'value' => $userInfo->country
					],
					'latitude' => [
						'type' => 'text',
						'label' => 'ละติจูด',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => $userInfo->latitude
					],
					'longitude' => [
						'type' => 'text',
						'label' => 'ลองกิจูด',
						'class' => '-fill',
						'maxlength' => 10,
						'value' => $userInfo->longitude
					],
					'phone' => [
						'type' => 'text',
						'label' => 'โทรศัพท์',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => $userInfo->phone
					],
					'mobile' => [
						'type' => 'text',
						'label' => 'โทรศัพท์เคลื่อนที่',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => $userInfo->mobile
					],
					'fax' => [
						'type' => 'text',
						'label' => 'โทรสาร',
						'class' => '-fill',
						'maxlength' => 20,
						'value' => $userInfo->fax
					],
					'website' => [
						'type' => 'text',
						'label' => 'เว็บไซต์',
						'class' => '-fill',
						'maxlength' => 200,
						'value' => $userInfo->website
					],
					'about' => [
						'type' => 'textarea',
						'label' => 'ประวัติย่อ',
						'class' => '-fill',
						'rows' => 12,
						'value' => $userInfo->about
					],
					'save2' => [
						'type' => 'button',
						'value' =>
							'<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'container' => ['class' => '-sg-text-right'],
						'pretext' =>
							'<a class="sg-action btn -link -cancel" href="'
							. \Url::link('my')
							. '" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a> '
					]
				] // children
			) // Form
		);
	}
}
?>
