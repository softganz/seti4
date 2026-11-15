<?php
/**
 * My      :: My Information API
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-07-11
 * Modify  :: 2025-04-05
 * Version :: 7
 *
 * @param String $action
 * @return Array/Object
 *
 * @usage api/my/{action}
 */

class MyApi extends PageApi {
	public $actionDefault = 'info';

	function __construct(?string $action = null) {
		parent::__construct([
			'action' => $action
		]);
	}

	function rightToBuild() {
		if (!i()->ok) {
			return apiError(_HTTP_ERROR_UNAUTHORIZED, 'Not sign in');
		}

		return true;
	}

	function info() {
		return (object) [
			'id' => i()->uid,
			'username' => i()->username,
			'name' => i()->name,
			'email' => i()->email,
			'photo' => BasicModel::user_photo(i()->username),
			'roles' => (array) i()->roles
		];
	}

	function passwordChange() {
		$post = (object) post('profile', _TRIM);

		$userInfo = R::model('user.get', i()->uid);

		if (!($post->current && $post->password && $post->repassword)) {
			return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');
		}

		if ($post->current === '') {
			return error(_HTTP_ERROR_BAD_REQUEST, 'กรุณาระบุรหัสผ่านปัจจุบัน');
		}

		if (
			!($post->current === sg_decrypt($userInfo->password, cfg('encrypt_key')))
		) {
			return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'รหัสผ่านปัจจุบันไม่ถูกต้อง');
		}

		// if (strlen($post->password) < 6) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'รหัสผ่านใหม่ต้องตัวอักษรอย่างน้อย 6 อักษร');

		if (!UserValidModel::checkPasswordValid($post->password, $passwordError)) {
			return error(_HTTP_ERROR_NOT_ACCEPTABLE, implode(',', $passwordError));
		}

		if (
			strlen($post->password) != strlen($post->repassword) ||
			$post->password != $post->repassword
		) {
			return error(
				_HTTP_ERROR_NOT_ACCEPTABLE,
				'การป้อนรหัสผ่านใหม่ทั้งสองครั้งไม่ตรงกัน'
			);
		}

		UserModel::updatePassword(i()->uid, $post->password);

		return apiSuccess('New password was change : บันทึกรหัสผ่านใหม่เรียบร้อย');
	}

	function accountDelete() {
		if (!\SG\confirm()) {
			return error(_HTTP_ERROR_BAD_REQUEST, 'กรุณายืนยัน');
		}

		UserModel::deleteAccount(i()->uid);

		return apiSuccess('ลบบัญชีเรียบร้อย');
	}

	function detailSave() {
		$profile = (object) \Request::post('profile');
		// debugMsg($profile, '$profile');

		if (empty($profile->name)) {
			return apiError(_HTTP_ERROR_BAD_REQUEST, 'กรุณาป้อนนามแฝง');
		}
		if (empty($profile->email)) {
			return apiError(_HTTP_ERROR_BAD_REQUEST, 'กรุณาป้อนอีเมล์');
		}
		if (!sg_is_email($profile->email)) {
			return apiError(
				_HTTP_ERROR_BAD_REQUEST,
				'กรุณาป้อนอีเมล์ให้ถูกต้องตามรูปแบบ เช่น yourname@domain.com'
			);
		}

		// Check duplicate email
		if (
			\DB::select(
				'SELECT `uid` FROM %users% WHERE `email` = :email AND `uid` != :uid LIMIT 1',
				var: [
					':email' => $profile->email,
					':uid' => i()->uid
				]
			)->valueOf('uid')
		) {
			return apiError(
				_HTTP_ERROR_BAD_REQUEST,
				'อีเมล์ <strong><em>'
					. $profile->email
					. '</em></strong> ได้มีการลงทะเบียนไว้แล้ว หรือ <a href="'
					. \Url::link('user/password')
					. '">ท่านจำรหัสผ่านไม่ได้</a>'
			);
		}

		// Check valid email
		if (sg_invalid_poster_name($profile->name)) {
			apiError(
				_HTTP_ERROR_BAD_REQUEST,
				'Duplicate name : มีผู้อื่นใช้ชื่อ <em>'
					. $profile->name
					. '</em> ไปแล้ว กรุณาเปลี่ยนเป็นชื่ออื่น'
			);
		}

		$profile->uid = i()->uid;
		$stmt = \mydb::create_update_cmd('%users%', $profile, '`uid` = :uid');
		\mydb::query($stmt, $profile);

		return apiSuccess('บันทึกเรียบร้อย');
	}

	function photoSave() {
		$photo = $_FILES['photo'];
		$error = false;

		if (empty($photo)) {
			return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่มีไฟล์ภาพ');
		}

		if (empty($photo['name'])) {
			return apiError(_HTTP_ERROR_BAD_REQUEST, 'กรุณาเลือกไฟล์ภาพถ่าย');
		}
		if (!in_array($photo['type'], ['image/jpeg', 'image/pjpeg'])) {
			return apiError(
				_HTTP_ERROR_BAD_REQUEST,
				'ประเภทของไฟล์ภาพถ่ายไม่ถูกต้อง ต้องเป็นภาพประเภท jpg เท่านั้น'
			);
		}

		if ($photo['size'] > 60 * 1024) {
			sg_photo_resize($photo['tmp_name'], 200, null, null, true, 80);
		}
		$photo_ext =
			$photo['type'] === 'image/gif'
				? 'gif'
				: ($photo['type'] === 'image/png'
					? 'png'
					: 'jpg');
		$upload_folder = cfg('upload.folder') . i()->username . '/';
		$upload_file = $upload_folder . 'profile.photo.' . $photo_ext;

		if (!file_exists($upload_folder)) {
			mkdir($upload_folder);
			if (cfg('upload.folder.chmod')) {
				chmod($upload_folder, cfg('upload.folder.chmod'));
			}
		}

		if (!move_uploaded_file($photo['tmp_name'], $upload_file)) {
			return apiError(
				_HTTP_ERROR_SERVER_ERROR,
				'มีข้อผิดพลาดทำให้ไม่สามารถบันทึกไฟล์ภาพได้'
			);
		}

		// change mode to config->upload.file.chmod
		cropImage(200, 200, $upload_file, $photo_ext, $upload_file);
		cropImage(
			50,
			50,
			$upload_file,
			$photo_ext,
			'file/' . i()->username . '/small.avatar.jpg'
		);

		if (cfg('upload.file.chmod')) {
			chmod($upload_file, cfg('upload.file.chmod'));
		}

		return apiSuccess('บันทึกภาพเรียบร้อย');
	}
}
?>
