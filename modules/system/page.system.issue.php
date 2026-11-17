<?php
/**
 * System   :: Sysem Issue Page Controller
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-10-20
 * Modified :: 2026-11-17
 * Version  :: 3
 *
 * @param int $issueId
 * @param string $action
 *
 * @uses module[/{id}/{action}/{tranId}]
 */

use Softganz\DB;

class SystemIssue extends PageController {
	public $issueId;

	function __construct($issueId = null, $action = null) {
		if (empty($issueId) && empty($action)) {
			$action = 'home';
		} elseif ($issueId && empty($action)) {
			$action = 'view';
		}
		parent::__construct([
			'issueId' => $issueId,
			'action' => 'system.issue.' . $action,
			'args' => func_get_args(),
			'info' => is_numeric($issueId) ? $this->getIssue($issueId) : null
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
