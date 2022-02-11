<?php
/**
* Module :: Page Controller
* Created 2021-09-26
* Modify 	2021-09-26
*
* @param Int $orgId
* @param String $action
* @return Widget
*
* @usage module[/{id}/{action}/{tranId}]
*/

$debug = true;

import('model:org.php');

class ProjectAdminOrg extends Page {
	var $orgId;
	var $action;
	var $_args = [];
	var $orgInfo;

	function __construct($orgId = NULL, $action = NULL) {
		$this->orgId = $orgId;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('Id '.$this->orgId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// $isAccess = $orgInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $orgInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		$orgInfo = is_numeric($this->orgId) ? OrgModel::get($this->orgId, '{debug: false}') : NULL;

		if (empty($this->orgId) && empty($this->action)) $this->action = 'home';
		else if ($this->orgId && empty($this->action)) $this->action = 'view';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$groupId.' , Action = '.$this->action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.$this->_args[$argIndex]);
		//debugMsg($this->_args, '$args');

		return R::PageWidget(
			'project.admin.org.'.$this->action,
			[-1 => $orgInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>