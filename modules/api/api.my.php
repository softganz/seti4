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
	var $action;
	var $actionDefault = 'info';

	function __construct($action = NULL) {
		parent::__construct([
			'action' => $action,
		]);
	}

	function rightToBuild() {
		if (!i()->ok) return apiError(_HTTP_ERROR_UNAUTHORIZED, 'Not sign in');

		return true;
	}

	function info() {
		return (Object) [
			'id' => i()->uid,
			'username' => i()->username,
			'name' => i()->name,
			'email' => i()->email,
			'photo' => BasicModel::user_photo(i()->username),
			'roles' => (Array) i()->roles,
		];
	}

	function passwordChange() {
		$post = (Object) post('profile', _TRIM);

		$userInfo = R::Model('user.get', i()->uid);

		if (!($post->current && $post->password && $post->repassword)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');

		if ($post->current === '' ) return error(_HTTP_ERROR_BAD_REQUEST, 'กรุณาระบุรหัสผ่านปัจจุบัน');

		if (!($post->current === sg_decrypt($userInfo->password,cfg('encrypt_key')))) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'รหัสผ่านปัจจุบันไม่ถูกต้อง');

		// if (strlen($post->password) < 6) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'รหัสผ่านใหม่ต้องตัวอักษรอย่างน้อย 6 อักษร');

		if (!UserValidModel::checkPasswordValid($post->password, $passwordError)) {
			return error(_HTTP_ERROR_NOT_ACCEPTABLE, implode(',', $passwordError));
		}

		if (strlen($post->password) != strlen($post->repassword) || $post->password != $post->repassword) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'การป้อนรหัสผ่านใหม่ทั้งสองครั้งไม่ตรงกัน');

		UserModel::updatePassword(i()->uid, $post->password);

		return apiSuccess('New password was change : บันทึกรหัสผ่านใหม่เรียบร้อย');
	}

	function accountDelete() {
		debugMsg(post(), 'post()');
		if (!\SG\confirm()) return error(_HTTP_ERROR_BAD_REQUEST, 'กรุณายืนยัน');
		UserModel::deleteAccount(i()->uid);

		return apiSuccess('ลบบัญชีเรียบร้อย');
	}
}
?>