<?php
/**
* iMed :: Psychiatry Controller
* Created 2021-05-26
* Modify  2021-05-31
*
* @param Int $psnId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage imed/psyc/{psnId}/{action[.action]}/{tranId}
*/

$debug = true;

import('model:imed.patient');

class iMedPsyc {
	var $psnId;
	var $args = [];
	function __construct($psnId = NULL, $action = NULL, $tranId = NULL) {
		$this->psnId = $psnId;
		$this->action = $action;
		$this->tranId = $tranId;
		$this->args = func_get_args();
	}

	function build() {
		if (!is_numeric($this->psnId)) {$this->action = $this->psnId; unset($this->psnId);} // Action as psnId and clear

		if (is_numeric($this->psnId)) {
			$patientInfo = PatientModel::get($this->psnId);
		}

		if (empty($this->psnId) && empty($this->action)) $this->action = 'home';
		else if ($this->psnId && empty($this->action)) $this->action = 'info.home';
		//if (empty($Info)) $Info = $mainId;

		// debugMsg($this,'$this');
		// debugMsg($patientInfo, '$patientInfo');
		$argIndex = 2; // Start argument

		// debugMsg('PAGE CONTROLLER Id = '.$this->psnId.' , Action = '.$this->action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.$this->args[$argIndex]);
		// debugMsg($this->args, '$args');

		$ret = R::Page(
			'imed.psyc.'.$this->action,
			$patientInfo,
			$this->args[$argIndex],
			$this->args[$argIndex+1],
			$this->args[$argIndex+2],
			$this->args[$argIndex+3],
			$this->args[$argIndex+4]
		);

		//debugMsg('TYPE = '.gettype($ret));
		if (is_null($ret)) $ret = message('error', 'ขออภัย!!! ไม่เจอหน้าที่ต้องการอยู่ระบบ');

		return $ret;
	}
}
?>