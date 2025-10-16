<?php
/**
* My      :: Change Profile Photo
* Created :: 20xx-xx-xx
* Modify  :: 2025-09-29
* Version :: 3
*
* @param String $args
* @return Widget
*
* @usage my/change/photo
*/

class MyChangePhoto extends Page {
	function build() {
		$photo = $_FILES['photo'];

		if ($photo) {
			$ret .= 'UPLAOD';
			if (empty($photo['name'])) $error[]='กรุณาเลือกไฟล์ภาพถ่าย';
			else {
				if (!in_array($photo['type'],array('image/jpeg','image/pjpeg'))) $error[]='ประเภทของไฟล์ภาพถ่ายไม่ถูกต้อง ต้องเป็นภาพประเภท jpg เท่านั้น';
				//				if ($photo['size'] >cfg('photo.max_file_size')*1024) $error[]='ขนาดไฟล์เกินค่าที่กำหนด คือ <em>'.cfg('photo.max_file_size').'KB</em>.';
			}

			if (!$error) {
				if ($photo['size'] >60*1024) sg_photo_resize($photo['tmp_name'],200,NULL,NULL,true,80);
				$photo_file['name']=sg_valid_filename($photo_file['name']);
				$photo_ext=$photo['type']==='image/gif' ? 'gif' : ($photo['type']==='image/png'?'png' : 'jpg');
				$upload_folder=cfg('upload.folder').i()->username.'/';
				$upload_file=$upload_folder.'profile.photo.'.$photo_ext;

				if (!file_exists($upload_folder)) {
					mkdir($upload_folder);
					if (cfg('upload.folder.chmod')) chmod($upload_folder,cfg('upload.folder.chmod'));
				}

				if (move_uploaded_file($photo['tmp_name'], $upload_file)) {
					// change mode to config->upload.file.chmod
					cropImage(200, 200, $upload_file, $photo_ext, $upload_file);
					cropImage(50, 50, $upload_file, $photo_ext, "file/".i()->username."/small.avatar.jpg");

					//echo $upload_file.":".i()->username."<br>";
					if (cfg('upload.file.chmod')) chmod($upload_file,cfg('upload.file.chmod'));
					location('my');
					$ret.=message('status','บันทึกไฟล์รูปถ่ายใหม่เรียบร้อย');
				} else {
					$error[]='มีข้อผิดพลาดทำให้ไม่สามารถบันทึกไฟล์ภาพได้';
				}
			}
		}

		if ($error) {
			header('HTTP/1.0 406 Not Acceptable');
			$ret.=message('error',$error);
		}
					
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Change Photo @'.i()->name,
			]), // AppBar
			'sideBar' => new SideBar([
				'style' => 'width: 200px;',
				'child' => R::View('my.menu'),
			]),
			'body' => new Widget([
				'children' => [
					new ListTile(['title' => 'Change Photo']),
					new Form([
						'action' => url('my/change/photo'), 'edit-profile',
						'class' => 'sg-form -upload',
						'enctype' => 'multipart/form-data',
						'rel' => _AJAX ? 'box' : '#main',
						'done' => _AJAX ? 'close | reload' : 'close | reload:'.url('my'),
						'children' => [
							'photo' => [
								'type' => 'file',
								'label' => 'Select photo file to upload',
								'size' => 50,
								'description' => '<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong> : ไฟล์ภาพประเภท jpg ขนาดภาพควรมีขนาดกว้างยาว 200x200 pixels , รูปถ่ายใหม่จะถูกบันทึกแทนที่รูปถ่ายเดิม',
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">cloud_upload</i><span>UPLOAD PHOTO</span>',
								'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('my').'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
								'container' => array('class'=>'-sg-text-right'),
							],
						], // children
					]), // Form
				], // children
			]), // Widget
		]);
	}
}
function my_change_photo($self) {
	$self->theme->sidebar = R::View('my.menu')->build();

	R::View('toolbar', $self, 'Change Photo @'.i()->name);

	$ret='<h3>Change Photo</h3>';



	$ret .= $form->build();

	return $ret;
}

?>