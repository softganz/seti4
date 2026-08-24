<?php
/**
 * System   :: Sysem Issue Page Controller
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-10-20
 * Modified :: 2028-08-24
 * Version  :: 2
 *
 * @param Int $issueId
 * @param String $action
 * @return Widget
 *
 * @uses module[/{id}/{action}/{tranId}]
 */

use Softganz\DB;

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

	public function getIssue($issueId) {
		return DB::select([
			'SELECT *
			FROM %system_issue%
			WHERE `issueId` = :issueId
			LIMIT 1',
			'var' => [':issueId' => $issueId]
		]);
	}
}
?>