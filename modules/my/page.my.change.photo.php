<?php
/**
 * My       :: Change Profile Photo
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 20xx-xx-xx
 * Modified :: 2026-11-15
 * Version  :: 5
 *
 * @uses my/change/photo
 */

declare(strict_types=1);

namespace My;

use My\Widgets\MySidebarWidget;

class MyChangePhoto extends \Page {
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
		return new \Scaffold(
			appBar: new \AppBar(title: 'Change Photo @' . i()->name), // AppBar
			// 'sideBar' => callFromApp() ? null : R::view('my.menu'),
			sideBar: new MySidebarWidget(),
			body: new \Widget(
				children: [
					new \Header(title: 'Change Photo', leading: new \Icon('add_a_photo')),
					new \Form(
						action: \Url::link('api/my/photo.save'),
						class: 'sg-form -upload',
						enctype: 'multipart/form-data',
						rel: _AJAX ? 'box' : '#main',
						done: _AJAX
							? 'close | reload'
							: 'close | reload:' . \Url::link('my'),
						children: [
							'photo' => [
								'type' => 'file',
								'label' => 'Select photo file to upload',
								'size' => 50,
								'description' =>
									'<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong> : ไฟล์ภาพประเภท jpg ขนาดภาพควรมีขนาดกว้างยาว 200x200 pixels , รูปถ่ายใหม่จะถูกบันทึกแทนที่รูปถ่ายเดิม'
							],
							'save' => [
								'type' => 'button',
								'value' =>
									'<i class="icon -material">cloud_upload</i><span>UPLOAD PHOTO</span>',
								'pretext' =>
									'<a class="sg-action btn -link -cancel" href="'
									. \Url::link('my')
									. '" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
								'container' => ['class' => '-sg-text-right']
							]
						] // children
					) // Form
				] // children
			) // Widget
		);
	}
}
?>
