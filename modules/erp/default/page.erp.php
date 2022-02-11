<?php
/**
* ERP :: Page Controller
* Created 2021-12-01
* Modify 	2021-12-01
*
* @param Int $orgId
* @param String $action
* @param Int $tranId
* @return Widget
*
* @usage module[/{id}/{action}/{tranId}]
*/

import('model:org.php');

class Erp extends Page {
	var $orgId;
	var $action;
	var $_args = [];

	function __construct($orgId = NULL, $action = NULL) {
		$this->orgId = $orgId;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('Id '.$this->orgId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$orgInfo = is_numeric($this->orgId) ? OrgModel::get($this->orgId, '{initTemplate: true, debug: false}') : NULL;
		$isAccess = $orgInfo->is->membership ? true : false;

		if ($this->orgId && !$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);

		if (empty($this->orgId) && empty($this->action)) $this->action = 'home';
		else if ($this->orgId && empty($this->action)) $this->action = 'info.home';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->orgId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		//debugMsg($this->_args, '$args');

		return R::PageWidget(
			'erp.'.$this->action,
			[-1 => $orgInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>