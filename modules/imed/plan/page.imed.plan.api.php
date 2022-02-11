<?php
/**
* iMed Care :: Care Plan API
* Created 2021-08-27
* Modify 	2021-08-27
*
* @param Int $planId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/{id}/{action}[/{tranId}]
*/

$debug = true;

import('model:imed.plan');

class ImedPlanApi extends Page {
	var $planId;
	var $action;
	var $tranId;

	function __construct($planId, $action, $tranId = NULL) {
		$this->planInfo = ImedPlanModel::get($planId);
		$this->planId = $this->planInfo->planId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		$planId = $this->planId;
		$tranId = $this->tranId;
		// debugMsg('Id '.$this->planId.' Action = '.$this->action.' TranId = '.$this->tranId);
		// debugMsg($this->planInfo,'$this->planInfo');

		if (empty($this->planId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		$isAccess = $this->planInfo->RIGHT & _IS_ACCESS;
		$isEdit = $this->planInfo->RIGHT & _IS_EDITABLE;

		if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'tran.remove' :
				if ($tranId && SG\confirm()) {
					mydb::query('DELETE FROM %imed_careplantr% WHERE `cptrid` = :tranId LIMIT 1', ':tranId', $tranId);
				}
				break;

			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>