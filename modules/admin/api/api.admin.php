<?php
/**
 * Admin    :: Admin General API
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-12-27
 * Modified :: 2026-11-17
 * Version  :: 6
 *
 * @param string $action
 *
 * @uses api/admin/{action}
 */

class AdminApi extends PageApi {
	function __construct($action) {
		parent::__construct([
			'action' => $action
		]);
	}

	// function rightToBuild() {return true;}
	function saveUserAccess() {
		if (!($access = post('access'))) {
			return apiError(_HTTP_ERROR_BAD_REQUEST, 'ข้อมูลไม่ครบถ้วน');
		}

		$roles = cfg('roles');

		foreach ($roles as $role_name => $role_perm) {
			if (isset($access[$role_name])) {
				asort($access[$role_name]);
				$roles->$role_name = implode(',', $access[$role_name]);
			} else {
				$roles->$role_name = '';
			}
		}
		cfg_db('roles', $roles);
		return 'บันทึกการเปลี่ยนแปลงเรียบร้อย.';
	}

	function moduleAdd() {
		$module = Request::post('module');
		if (empty($module)) {
			return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่ระบุชื่อโมดูล');
		}

		$message = process_install_module($module);

		if ($message === false) {
			return apiError(
				_HTTP_ERROR_NOT_ACCEPTABLE,
				'Module "' . $module . '" not found.'
			);
		}

		return apiSuccess(
			'Module "' . $module . '" install completed.<br>' . $message
		);
	}
}
?>
