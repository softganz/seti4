<?php
/**
* Module  :: Page Controller
* Created :: 2022-10-20
* Modify  :: 2022-10-20
* Version :: 1
*
* @param Int $issueId
* @param String $action
* @return Widget
*
* @usage module[/{id}/{action}/{tranId}]
*/

class SystemIssue extends PageController {
	var $issueId;
	var $action;

	function __construct($issueId = NULL, $action = NULL) {
		if (empty($issueId) && empty($action)) $action = 'home';
		else if ($issueId && empty($action)) $action = 'view';
		parent::__construct([
			'issueId' => $issueId,
			'action' => 'system.issue.'.$action,
			'args' => func_get_args(),
			'info' => is_numeric($issueId) ? $this->getIssue($issueId) : NULL,
		]);
	}

	function build() {
		// debugMsg('Id '.$this->issueId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// $isAccess = $issueInfo->RIGHT & _IS_ACCESS;

		// if (!$isAccess) {
		// return new ErrorMessage(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// }

		return parent::build();
	}

	public function getIssue($issueId) {
		return mydb::clearprop(mydb::select(
			'SELECT *
			FROM %system_issue%
			WHERE `issueId` = :issueId
			LIMIT 1',
			[':issueId' => $issueId]
		));
	}
}
?>