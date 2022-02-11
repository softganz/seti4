<?php
/**
* iMed :: Group Page Controller
* Created 2021-08-17
* Modify  2021-08-20
*
* @param Int $groupId
* @param String $action
* @return Widget
*
* @usage imed/group/{id}/{action}/{tranId}
*/

$debug = true;

import('model:imed.group');

class ImedGroup extends Page {
	var $refApp;
	var $urlHome = 'patient';
	var $groupId;
	var $action;
	var $_args = [];

	function __construct($groupId = NULL, $action = NULL, $tranId = NULL) {
		$this->groupId = $groupId;
		$this->action = $action;
		$this->_args = func_get_args();
		parent::__construct();
	}

	function build() {
		$isAdmin = is_admin('imed');
		// $isOfficer = $isAdmin || user_access('access ibuys customer');

		// if (!$isOfficer) return message('error', 'Access Denied');

		// if (!is_numeric($groupId)) {$action = $groupId; unset($groupId);} // Action as mainId and clear
		$groupInfo = is_numeric($this->groupId) ? ImedGroupModel::get($this->groupId, '{debug: false}') : NULL;

		if (empty($this->action) && empty($this->groupId)) $this->action = 'home';
		else if (empty($this->action) && $this->groupId) $this->action = $this->urlHome;
		// if (empty($Info)) $Info = $this->groupId;

		$argIndex = 2; // Start argument

		// debugMsg('PAGE CONTROLLER Id = '.$groupId.' , Action = '.$this->action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.$this->_args[$argIndex]);
		// debugMsg($this->_args, '$args');

		$ret = R::Page(
			// 'imed'.($this->refApp ? '.'.$this->refApp : '').'.group.'.$this->action,
			'imed.group.'.$this->action,
			$groupInfo,
			$this->_args[$argIndex],
			$this->_args[$argIndex+1],
			$this->_args[$argIndex+2],
			$this->_args[$argIndex+3],
			$this->_args[$argIndex+4]
		);

		//debugMsg('TYPE = '.gettype($ret));
		if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

		return $ret;
	}
}
?>