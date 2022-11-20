<?php
/**
* Module  :: Description
* Created :: 2022-10-14
* Modify  :: 2022-10-14
* Version :: 1
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

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

		if ($issueId = $this->tranId) {
			mydb::query(
				'UPDATE %system_issue%
				SET `status` = :status
				WHERE `issueId` = :issueId
				LIMIT 1',
				[
					':issueId' => $issueId,
					':status' => _COMPLETE,
				]
			);
		}
	}
}
?>