<?php
/**
* Admin   :: Admin General API
* Created :: 2021-12-27
* Modify  :: 2024-07-08
* Version :: 3
*
* @param String $action
* @return Array
*
* @usage api/admin/{action}
*/

class AdminApi extends PageApi {
	var $action;

	function __construct($action) {
		parent::__construct([
			'action' => $action,
		]);
	}

	function saveUserAccess() {
		if (!$access = post('access')) return [
			'responseCode' => _HTTP_ERROR_BAD_REQUEST,
			'text' => 'ข้อมูลไม่ครบถ้วน',
		];

		$roles = cfg('roles');

		foreach ($roles as $role_name => $role_perm) {
			if (isset($access[$role_name])) {
				asort($access[$role_name]);
				$roles->$role_name = implode(',',$access[$role_name]);
			} else {
				$roles->$role_name = '';
			}
		}
		cfg_db('roles',$roles);
		return 'บันทึกการเปลี่ยนแปลงเรียบร้อย.';
	}
}
?>