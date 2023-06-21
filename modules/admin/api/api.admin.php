<?php
/**
* Admin   :: Admin General API
* Created :: 2021-12-27
* Modify  :: 2023-01-28
* Version :: 2
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

	function IpBan() {
		if (!($ip = post('ip'))) {
			return [
				'responseCode' => _HTTP_ERROR_BAD_REQUEST,
				'text' => 'ข้อมูลไม่ครบถ้วน',
			];
		}

		$banIpList = cfg('ban.ip');
		if (!is_object($banIpList)) $banIpList = (Object) [];
		$banTime = \SG\getFirst(post('time'), cfg('ban.time'), 1*24*60); // Ban time in minute
		$banIpList->{$ip} = (Object) [
			'start' => date('Y-m-d H:i:s'),
			'end' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +'.$banTime.' minutes')),
		];
		$ret .= 'BAN IP '.$ip;
		cfg_db('ban.ip', \SG\json_encode($banIpList));
		return 'IP was banded.';
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