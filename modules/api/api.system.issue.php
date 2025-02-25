<?php
/**
* System  :: System API
* Created :: 2025-02-25
* Modify  :: 2025-02-25
* Version :: 1
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

		$issueType = post('type');
		$host = post('host');

		if ($issueId = $this->tranId) {
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
	}
}
?>