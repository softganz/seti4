<?php
/**
 * My       :: My Home
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-12-14
 * Modified :: 2026-11-15
 * Version  :: 8
 *
 * @usage my
 */

declare(strict_types=1);

namespace My;

use My\Widgets\MySidebarWidget;

class My extends \Page {
	protected object $right;

	function __construct() {
		parent::__construct();
		$this->right = (object) [
			'admin' => is_admin('user')
		];
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
		$uid = i()->uid;
		$userInfo = \UserModel::get($uid);

		return new \Scaffold(
			appBar: new \AppBar(
				title: '@' . i()->name,
				leading: _HEADER_BACK,
				trailing: $this->right->admin
					? new \Button(
						type: 'link',
						href: \Url::link('my'),
						class: 'sg-action',
						rel: '#main',
						icon: new \Icon('refresh')
					)
					: null
			), // AppBar
			sideBar: new MySidebarWidget(),
			body: new \Column(
				children: [
					$this->profilePhoto($userInfo),
					$this->profileDetail($userInfo),
					$this->stat($userInfo),
					'<script type="text/javascript">
					$(document).ready(function () {
						$(".NO-CACHE").attr("src",function () { return $(this).attr("src") + "?a=" + Math.random() });
					});
					</script>'
				] // children
			) // Widget
		);
	}

	/**
	 * Show profile photo
	 *
	 * @param object $userInfo
	 * @return object
	 */
	private function profilePhoto(object $userInfo): object {
		return new \ProfilePhotoWidget(
			photo: \UserModel::profilePhoto($userInfo->username),
			children: [
				new \Form(
					class: 'sg-form -upload -upload-profile-photo',
					enctype: 'multipart/form-data',
					action: \Url::link('api/my/photo.save'),
					rel: 'notify',
					done: 'load:#main:' . \Url::link('my'),
					children: [
						'<span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" onchange="$(this).closest(form).submit(); return false;"></span>'
					] // children
				), // Form

				$this->right->admin
					? new \Container(
						class: '-sg-text-center',
						child: 'ชื่อที่ใช้ในการเข้าระบบ'
							. ' : <strong><a href="'
							. \Url::link('admin/user/edit/' . $userInfo->uid)
							. '">'
							. $userInfo->username
							. '</a></strong>'
					)
					: null,

				user_access('administer users', 'change own profile', $userInfo->uid)
					? new \Row(
						mainAxisAlignment: 'center',
						class: '-py-16',
						style: 'gap: 16px;',
						children: [
							new \Button(
								type: 'link',
								href: \Url::link('my/change/detail'),
								icon: new \Icon('person'),
								text: tr('change your profile details')
							),
							new \Button(
								type: 'link',
								href: \Url::link('my/change/password'),
								icon: new \Icon('enhanced_encryption'),
								text: tr('change password')
							),
							new \Button(
								type: 'link',
								href: \Url::link('my/change/photo'),
								icon: new \Icon('add_a_photo'),
								text: tr('change photo')
							)
						]
					)
					: null
			] // children
		);
	}

	/**
	 * Show pgofile detail
	 *
	 * @param object $userInfo
	 * @return object
	 */
	private function profileDetail(object $userInfo): object {
		return new \Column(
			children: [
				$userInfo->real_name || $userInfo->mid_name || $userInfo->last_name
					? 'ชื่อจริง : '
						. implode(' ', [
							$userInfo->name_prefix ? $userInfo->name_prefix . ' ' : '',
							$userInfo->real_name,
							$userInfo->mid_name ? ' (' . $userInfo->mid_name . ')' : '',
							$userInfo->last_name
						])
					: '',
				$userInfo->occupation ? 'อาชีพ : ' . $userInfo->occupation : null,
				$userInfo->position ? 'ตำแหน่ง : ' . $userInfo->position : null,
				$userInfo->organization
					? 'องค์กร / บริษัท : ' . $userInfo->organization
					: null,
				$userInfo->address || $userInfo->amphur || $userInfo->province
					? 'ที่อยู่ : '
						. implode(' ', [
							$userInfo->address,
							$userInfo->amphur,
							$userInfo->province,
							$userInfo->zipcode,
							$userInfo->country
						])
					: null,
				$userInfo->latitude ? 'ละติจูด : ' . $userInfo->latitude : null,
				$userInfo->longitude ? 'ลองกิจูด : ' . $userInfo->longitude : null,
				$userInfo->phone &&
				(user_access('administer users') || i()->uid == $userInfo->uid)
					? 'โทรศัพท์ : ' . $userInfo->phone
					: null,
				$userInfo->mobile &&
				(user_access('administer users') || i()->uid == $userInfo->uid)
					? 'โทรศัพท์เคลื่อนที่ : ' . $userInfo->mobile
					: null,
				$userInfo->fax ? 'แฟกซ์ : ' . $userInfo->fax : null,
				$userInfo->website
					? 'เว็บไซท์ : '
						. '<a href="'
						. $userInfo->website
						. '" target="_blank">'
						. $userInfo->website
						. '</a><br /><br />'
					: null,
				$userInfo->about
					? 'คำหลัก : ' . 'ประวัติย่อ' . ' : ' . sg_text2html($userInfo->about)
					: null
			]
		);
	}

	/**
	 * Show user statistic
	 *
	 * @param object $userInfo
	 * @return object
	 */
	private function stat(object $userInfo): object {
		return new \Column(
			class: '-sg-text-right',
			children: [
				'เริ่มเป็นสมาชิกตั้งแต่ วัน'
				. sg_date($userInfo->datein, 'ววว ว ดด ปป H:i')
				. ' น.',
				$userInfo->login_time
					? 'เข้าระบบล่าสุดเมื่อ วัน'
						. sg_date($userInfo->login_time, 'ววว ว ดด ปปปป H:i')
						. ' น.'
					: null,
				'เข้าชมเว็บไซท์ : ' . number_format($userInfo->hits) . ' ครั้ง',
				'อ่าน : ' . number_format($userInfo->views) . ' ครั้ง'
			] // children
		);
	}
}
?>
