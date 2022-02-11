<?php
/**
* iMed :: Care Seav
* Created 2021-07-30
* Modify  2021-07-30
*
* @return Widget
*
* @usage imed/care
*/

$debug = true;

import('package:imed/care/models/model.request.php');

class ImedCareServ extends Page {
	var $keyId;
	private $_args = [];

	function __construct($keyId = NULL, $action = NULL) {
		$this->keyId = $keyId;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		// if (!is_numeric($this->psnId)) {$this->action = $this->psnId; unset($this->psnId);} // Action as psnId and clear

		$requestInfo = RequestModel::get($this->keyId);

		if (!$requestInfo->reqId) return message('error', 'ไม่มีรายการที่ระบุ');
		else if (!$requestInfo->is->access) return message('error', 'Access Denied');

		if (empty($this->keyId) && empty($this->action)) $this->action = 'home';
		else if ($this->keyId && empty($this->action)) $this->action = 'info.home';

		$argIndex = 2; // Start argument

		// debugMsg('PAGE CONTROLLER Id = '.$this->keyId.' , Action = '.$this->action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.$this->_args[$argIndex]);
		// debugMsg($this->_args, '$_args');
		// debugMsg($this,'$this');
		// debugMsg($requestInfo, '$requestInfo');

		$ret = R::Page(
			'imed.care.serv.'.$this->action,
			$requestInfo,
			$this->_args[$argIndex],
			$this->_args[$argIndex+1],
			$this->_args[$argIndex+2],
			$this->_args[$argIndex+3],
			$this->_args[$argIndex+4]
		);

		//debugMsg('TYPE = '.gettype($ret));
		if (is_null($ret)) $ret = message('error', 'ขออภัย!!! ไม่เจอหน้าที่ต้องการอยู่ระบบ');

		return $ret;
	}
}
?>