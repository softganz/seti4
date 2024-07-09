<?php
/**
* Admin   :: Admin Ban API
* Created :: 2024-07-08
* Modify  :: 2024-07-09
* Version :: 3
*
* @param String $action
* @return Array
*
* @usage api/admin/ban/{action}
*/

class AdminBanApi extends PageApi {
	var $action;

	function __construct($action) {
		parent::__construct([
			'action' => $action,
		]);
	}

	// Add/Update ban ip/host
	function save() {
		$ip = SG\getFirst(post('ip'));
		$host = SG\getFirst(post('host'));
		$banTime = SG\getFirstInt(post('time'), cfg('ban.time'), 1*24*60); // Ban time in minute

		// debugMsg(post(), 'post()');

		if (empty($ip) && empty($host)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ข้อมูลไม่ครบถ้วน');

		$banList = (Array) cfg('ban.ip');

		if ($ip) {
			$banList[] = (Object) [
				'ip' => $ip,
				'start' => date('Y-m-d H:i:s'),
				'end' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +'.$banTime.' minutes')),
			];
		}

		if ($host) {
			$banList[] = (Object) [
				'host' => $host,
				'start' => date('Y-m-d H:i:s'),
				'end' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +'.$banTime.' minutes')),
			];
		}

		// debugMsg($banList, '$banList');
		cfg_db('ban.ip', SG\json_encode($banList));
		return apiSuccess('IP was banded.');
	}

	// Remove ban item
	function remove() {
		$id = SG\getFirstInt(post('id'));
		if (!SG\confirm()) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ข้อมูลไม่ครบถ้วน');
		if (is_null($id)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ข้อมูลไม่ครบถ้วน');

		$banList = (Array) cfg('ban.ip');
		unset($banList[$id]);
		cfg_db('ban.ip', $banList);
		debugMsg('id'.$id);
		return 'DELETE';
	}
}
?>