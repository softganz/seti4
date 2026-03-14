<?php
/**
* System  :: System API
* Created :: 2022-10-14
* Modify  :: 2025-03-14
* Version :: 4
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage api/system/{action}[/{tranId}]
*/

use Softganz\DB;

class SystemApi extends PageApi {
	var $action;
	var $tranId;
	var $right;

	function __construct($action = NULL, $tranId = NULL) {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			'right' => (Object) [
				'admin' => is_admin(),
			],
		]);
	}

	private function _accessDenied() {
		return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
	}

	function info() {
		header('Access-Control-Allow-Origin: *');
		return apiSuccess([
			'coreName' => 'Seti',
			'coreVersion' => cfg('core.version'),
			'databaseVersion' => cfg('version.install'),
			'date' => date('Y-m-d'),
			'time' => date('H:i:s'),
			'online' => CounterModel::onlineMemberCount(),
			'member' => CounterModel::onlineCount(),
			'ip' => $_SERVER['REMOTE_ADDR'],
		]);
	}

	function date() {
		return date('Y-m-d H:i:s');
	}
}
?>