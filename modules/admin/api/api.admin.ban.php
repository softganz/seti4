<?php
/**
 * Admin   :: Admin Ban API
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2024-07-08
 * Modify  :: 2025-12-29
 * Version :: 4
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

	// Add or Update ban ip/host
	function save() {
		$banTime = SG\getFirstInt(Request::all('time'), cfg('ban.time'), 1*24*60);

		$data = (Object) [
			'ip' => SG\getFirst(Request::all('ip')),
			'host' => SG\getFirst(Request::all('host')),
			'end' => date(
				'Y-m-d H:i:s',
				strtotime(date('Y-m-d H:i:s') . ' +' . $banTime . ' minutes')
			), // Convert ban time in minute to date and time
		];


		if (empty($data->ip) && empty($data->host)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ข้อมูลไม่ครบถ้วน');

		BanModel::save($data);

		return apiSuccess('IP/Host was banded.');
	}

	// Remove ban item
	function remove() {
		$id = SG\getFirstInt(post('id'));
		if (!SG\confirm()) return apiError(_HTTP_ERROR_BAD_REQUEST, 'กรุณายืนยันการลบรายการ');
		if (is_null($id)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ข้อมูลไม่ครบถ้วน');

		BanModel::remove($id);

		return apiSuccess('Ban IP/Host was deleted.');
	}
}
?>