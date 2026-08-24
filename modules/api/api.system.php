<?php
/**
 * System   :: System API
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-10-14
 * Modified :: 2026-08-24
 * Version  :: 6
 *
 * @param Int $mainId
 * @param String $action
 * @param Int $tranId
 * @return String
 *
 * @uses api/system/{action}[/{tranId}]
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
			'domain' => _DOMAIN,
			'coreName' => 'Seti',
			'coreVersion' => cfg('core.version'),
			'databaseVersion' => cfg('version.install'),
			'date' => date('Y-m-d'),
			'time' => date('H:i:s'),
			'members' => CounterModel::onlineMemberCount(),
			'onlines' => CounterModel::onlineCount(),
			'ip' => $_SERVER['REMOTE_ADDR'],
		]);
	}

	function date() {
		return date('Y-m-d H:i:s');
	}

	function watchdogDelete() {
		$watchdogId = Request::all('id');

		if (!$this->right->admin) return apiError(_HTTP_ERROR_NOT_ALLOWED, _ERROR_MSG_ACCESS_DENIED);
		if (empty($watchdogId)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุ ID');
		if (!\SG\confirm()) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ยืนยัน');

		DB::query([
			'DELETE FROM %watchdog% WHERE `wid` = :watchdogId LIMIT 1',
			'var' => [':watchdogId' => $watchdogId]
		]);

		return apiSuccess('Watchdog deleted');
	}
}
?>