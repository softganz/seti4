<?php
function my_change_photo($self) {
	$self->theme->sidebar = R::View('my.menu')->build();

	R::View('toolbar', $self, 'Change Photo @'.i()->name);

	$ret='<h3>Change Photo</h3>';

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

			if (debug('profile')) error_reporting(E_ALL);
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

	$form = new Form(NULL, url('my/change/photo'), 'edit-profile', 'sg-form -upload');
	$form->addConfig('enctype', 'multipart/form-data');
	if (_AJAX) {
		$form->addData('rel','box');
		$form->addData('done', 'close | reload');
	} else {
		$form->addData('rel','#main');
		$form->addData('done', 'close | reload:'.url('my'));
	}

	$form->addField(
					'photo',
					array(
						'type' => 'file',
						'label' => 'Select photo file to upload',
						'size' => 50,
						'description' => '<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong> : ไฟล์ภาพประเภท jpg ขนาดภาพควรมีขนาดกว้างยาว 200x200 pixels , รูปถ่ายใหม่จะถูกบันทึกแทนที่รูปถ่ายเดิม',
					)
				);

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -upload -white"></i><span>UPLOAD PHOTO</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('my').'" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
						'container' => array('class'=>'-sg-text-right'),
					)
				);

	$ret .= $form->build();

	return $ret;
}

?>