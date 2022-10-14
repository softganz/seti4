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

class ApiSystem extends PageApi {
	var $action;
	var $tranId;
	var $right;

	function __construct($action = NULL, $tranId = NULL) {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			'right' => (Object) [
				'edit' => is_admin(),
			],
		]);
	}

	function build() {
		if (!$this->right->edit) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_FORBIDDEN,
				'text' => 'Access Denied',
			]);
		}
		return parent::build();
	}

	public function issueClose() {
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
		debugMsg(mydb()->_query);
	}
}
?>