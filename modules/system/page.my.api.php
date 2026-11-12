<?php
/**
 * My API   :: My Information API
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-12-14
 * Modified :: 2026-11-12
 * Version  :: 4
 *
 * @uses my/api/{action}[/{tranId}]
 */

class MyApi extends Page {
	var $userId;
	var $action;
	var $tranId;

	function __construct($action = null, $tranId = null) {
		$this->userId = \SG\getFirst(post('userId'), i()->uid);
		$this->action = $action;
		$this->tranId = $tranId;
		$this->right = (object) [
			'edit' => (i()->ok && i()->uid == $this->userId) || is_admin()
		];
	}

	/**
	 * Right to build
	 *
	 * @return object|true
	 */
	function rightToBuild(): object|true {
		if (!i()->ok) {
			return new SignForm();
		}
		return true;
	}

	function build() {
		// debugMsg('mainId '.$this->mainId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$userId = $this->userId;
		$this->userInfo = UserModel::get($userId);

		if (empty($this->userInfo)) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_BAD_REQUEST,
				'text' => 'PROCESS ERROR'
			]);
		} elseif (!$this->right->edit) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_FORBIDDEN,
				'text' => 'Access Denied'
			]);
		}

		$ret = '';

		switch ($this->action) {
			case 'profile.update':
				$profile = (object) post('profile', _TRIM + _STRIPTAG);

				if (empty($profile->name)) {
					$error[] = 'กรุณาป้อนนามแฝง';
				}
				if (empty($profile->email)) {
					$error[] = 'กรุณาป้อนอีเมล์';
				} elseif (!sg_is_email($profile->email)) {
					$error[] =
						'กรุณาป้อนอีเมล์ให้ถูกต้องตามรูปแบบ คือ yourname@domain.com';
				} elseif (
					$profile->email &&
					mydb::select(
						'SELECT `uid` FROM %users% WHERE `email` = :email AND `uid` != :uid LIMIT 1',
						':email',
						$profile->email,
						':uid',
						$this->userInfo->uid
					)->uid
				) {
					$error[] =
						'อีเมล์ <strong><em>'
						. $profile->email
						. '</em></strong> ได้มีการลงทะเบียนไว้แล้ว หรือ <a href="'
						. url('user/password')
						. '">ท่านจำรหัสผ่านไม่ได้</a>'; //-- duplicate email
				}
				if (sg_invalid_poster_name($profile->name)) {
					$error[] =
						'Duplicate name : มีผู้อื่นใช้ชื่อ <em>'
						. $profile->name
						. '</em> ไปแล้ว กรุณาเปลี่ยนเป็นชื่ออื่น';
				}

				if ($error) {
					return '<ul><li>' . implode('</li><li>', $error) . '</li></ul>';
				}

				$profile->changeuid = $this->userInfo->uid;
				$stmt = mydb::create_update_cmd(
					'%users%',
					$profile,
					'`uid` = :changeuid'
				);
				mydb::query($stmt, $profile);
				$ret .= 'บันทึกข้อมูลเรียบร้อย';
				//$ret .= R('query');
				break;

			case 'photo.change':
				$photo = $_FILES['photo'];
				$error = false;

				if ($photo) {
					if (empty($photo['name'])) {
						$ret .= 'กรุณาเลือกไฟล์ภาพถ่าย';
					} else {
						if (!in_array($photo['type'], ['image/jpeg', 'image/pjpeg'])) {
							$error =
								'ประเภทของไฟล์ภาพถ่ายไม่ถูกต้อง ต้องเป็นภาพประเภท jpg เท่านั้น';
						}
					}

					if (!$error) {
						if ($photo['size'] > 60 * 1024) {
							sg_photo_resize($photo['tmp_name'], 200, null, null, true, 80);
						}
						$photo_file['name'] = sg_valid_filename($photo_file['name']);
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

						if (move_uploaded_file($photo['tmp_name'], $upload_file)) {
							// change mode to config->upload.file.chmod
							cropImage(200, 200, $upload_file, $photo_ext, $upload_file);
							cropImage(
								50,
								50,
								$upload_file,
								$photo_ext,
								'file/' . i()->username . '/small.avatar.jpg'
							);

							//echo $upload_file.":".i()->username."<br>";
							if (cfg('upload.file.chmod')) {
								chmod($upload_file, cfg('upload.file.chmod'));
							}
							$ret .= 'บันทึกไฟล์รูปถ่ายใหม่เรียบร้อย';
						} else {
							$error = 'มีข้อผิดพลาดทำให้ไม่สามารถบันทึกไฟล์ภาพได้';
						}
					}
					if ($error) {
						$ret .= $error;
					}
				}
				break;

			case 'info.update':
				import('model:bigdata.php');
				$data = (object) [
					'key' => 'user/profile/' . $userId,
					'value' => (object) ['lineId' => 'ipanumas']
				];
				BigDataModel::updateJson(
					'user/profile/' . $userId,
					(object) ['lineId' => 'ipanumas', 'facebook' => 'softganz']
				);
				break;

			default:
				return new ErrorMessage([
					'responseCode' => _HTTP_ERROR_BAD_REQUEST,
					'text' => 'Action not found!!!'
				]);
				break;
		}

		return $ret;
	}
}
?>
