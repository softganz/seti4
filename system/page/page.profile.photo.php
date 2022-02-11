<?php
function profile_photo($self,$uid=NULL) {
	if (empty($uid)) $uid=i()->uid;

	$user = R::Model('user.get',$uid);

	if (!user_access('administer users','change own profile',$user->uid)) return message('error','Access denied');

	if ($_POST['cancel']) location('profile/'.$user->uid);

	R::View('profile.toolbar',$self,$user->uid);

	$ret='<h3>Change photo</h3>';

	$photo=$_FILES['photo'];

	if ($photo) {
		if (empty($photo['name'])) $error[]='กรุณาเลือกไฟล์ภาพถ่าย';
		else {
			if (!in_array($photo['type'],array('image/jpeg','image/pjpeg'))) $error[]='ประเภทของไฟล์ภาพถ่ายไม่ถูกต้อง ต้องเป็นภาพประเภท jpg เท่านั้น';
			//				if ($photo['size'] >cfg('photo.max_file_size')*1024) $error[]='ขนาดไฟล์เกินค่าที่กำหนด คือ <em>'.cfg('photo.max_file_size').'KB</em>.';
		}

		if ($error) {
			header('HTTP/1.0 406 Not Acceptable');
		} else {
			if ($photo['size'] >60*1024) sg_photo_resize($photo['tmp_name'],200,NULL,NULL,true,80);
			$photo_file['name']=sg_valid_filename($photo_file['name']);
			$photo_ext=$photo['type']==='image/gif' ? 'gif' : ($photo['type']==='image/png'?'png' : 'jpg');
			$upload_folder=cfg('upload.folder').$user->username.'/';
			$upload_file=$upload_folder.'profile.photo.'.$photo_ext;

			if (!file_exists($upload_folder)) {
				mkdir($upload_folder);
				if (cfg('upload.folder.chmod')) chmod($upload_folder,cfg('upload.folder.chmod'));
			}

			if (debug('profile')) error_reporting(E_ALL);
			if (move_uploaded_file($photo['tmp_name'], $upload_file)) {
				// change mode to config->upload.file.chmod
				cropImage(200, 200, $upload_file, $photo_ext, $upload_file);
				cropImage(50, 50, $upload_file, $photo_ext, "file/".$user->username."/small.avatar.jpg");

				//echo $upload_file.":".$user->username."<br>";
				if (cfg('upload.file.chmod')) chmod($upload_file,cfg('upload.file.chmod'));
				//location('profile/'.$user->uid);
				$ret.=message('status','บันทึกไฟล์รูปถ่ายใหม่เรียบร้อย');
			} else {
				$error[]='มีข้อผิดพลาดทำให้ไม่สามารถบันทึกไฟล์ภาพได้';
			}
		}
	}

	if ($error) $ret.=message('error',$error);

	$form = new Form('profile', url(q()), 'edit-profile', 'sg-form -upload');
	$form->config->enctype='multipart/form-data';

	if (_AJAX) {
		$form->addData('rel','box');
		$form->addData('done', 'close | load');
	} else {
		$form->addData('rel','#main');
	}

	$form->photo->name='photo';
	$form->photo->label='Select photo file to upload';
	$form->photo->type='file';
	$form->photo->size=50;
	$form->photo->description=sg_client_convert('<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong> : ไฟล์ภาพประเภท jpg ขนาดภาพควรมีขนาดกว้างยาว 200x200 pixels , รูปถ่ายใหม่จะถูกบันทึกแทนที่รูปถ่ายเดิม');

	$form->button->type='submit';
	$form->button->items->save='Upload photo';
	$form->button->items->cancel='Cancel';

	$ret .= $form->build();

	return $ret;
}

?>