<?php
/**
 * System  :: System API
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2025-02-25
 * Modify  :: 2025-12-29
 * Version :: 2
 *
 * @param String $action
 * @param Int $tranId
 * @return String
 *
 * @usage api/system/issue/{action}[/{tranId}]
 */

use Softganz\DB;

class SystemIssueApi extends PageApi {
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

	public function close() {
		if (!$this->right->admin) return $this->_accessDenied();

		$issueId = $this->tranId;
		$issueType = Request::all('type');
		$host = Request::all('host');

		if (empty($issueId)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่มีหมายเลข issue');

		DB::query([
			'UPDATE %system_issue%
			SET `status` = :status
			%WHERE%',
			'where' => [
				'%WHERE%' => [
					$issueId === '*' ? ['`status` = :draft', ':draft' => _START] : ['`issueId` = :issueId', ':issueId' => $issueId],
					$issueType ? ['`issueType` = :issueType', ':issueType' => $issueType] : NULL,
					$host ? ['`host` = :host', ':host' => $host] : NULL,
				]
			], // where
			'var' => [':status' => _COMPLETE]
		]);
	}

	public function delete() {
		if (!$this->right->admin) return $this->_accessDenied();

		$issueId = $this->tranId;

		if (empty($issueId)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่มีหมายเลข issue');

		DB::query([
			'DELETE FROM %system_issue%
			WHERE `issueId` = :issueId
			LIMIT 1',
			'var' => [':issueId' => $issueId]
		]);
	}
}
?>