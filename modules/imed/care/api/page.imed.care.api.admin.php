<?php
/**
* iMed Care :: Admin API
* Created 2021-09-17
* Modify  2021-09-17
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

$debug = true;

class ImedCareApiAdmin extends Page {
	var $mainId;
	var $action;
	var $tranId;

	function __construct($mainId, $action, $tranId = NULL) {
		$this->mainId = $mainId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg('mainId '.$this->mainId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// if (empty($this->mainId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $mainInfo->RIGHT & _IS_ACCESS;
		$isEdit = is_admin('imed care');

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'giver.disable' :
				mydb::query('UPDATE %users_role% SET `status` = "BLOCK" WHERE `uid` = :userId AND `role` = "IMED GIVER" LIMIT 1', ':userId', $this->mainId);
				break;

			case 'giver.enable' :
				mydb::query('UPDATE %users_role% SET `status` = "ENABLE" WHERE `uid` = :userId AND `role` = "IMED GIVER" LIMIT 1', ':userId', $this->mainId);
				break;

			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>