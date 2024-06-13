<?php
/**
* System  :: System API
* Created :: 2022-10-14
* Modify  :: 2024-06-13
* Version :: 2
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
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
		return [
			'responseCode' => _HTTP_ERROR_FORBIDDEN,
			'text' => 'Access Denied',
		];
	}

	function date() {
		return date('Y-m-d H:i:s');
	}

	public function issueClose() {
		if (!$this->right->admin) return $this->_accessDenied();

		$issueType = post('type');

		if ($issueId = $this->tranId) {
			DB::query([
				'UPDATE %system_issue%
				SET `status` = :status
				%WHERE%',
				'where' => [
					'%WHERE%' => [
						$issueId === '*' ? ['`status` = :draft', ':draft' => _START] : ['`issueId` = :issueId', ':issueId' => $issueId],
						$issueType ? ['`issueType` = :issueType', ':issueType' => $issueType] : NULL,
					]
				], // where
				'var' => [':status' => _COMPLETE]
			]);
		}
	}
}
?>