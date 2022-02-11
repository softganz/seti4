<?php
/**
* iMed :: Patient Page Controller
* Created 2019-03-05
* Modify  2021-08-19
*
* @param Int $psnId
* @param String $action
* @return String
*
* @usage module[/{id}/{action}/{tranId}]
*/

$debug = true;

import('model:imed.patient');

class ImedPatient extends Page {
	var $psnId;
	var $action;
	var $_args = [];

	function __construct($psnId = NULL, $action = NULL) {
		$this->psnId = $psnId;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('Id '.$this->psnId.' , Action = '.$this->action.' , TranId = '.$this->tranId);

		$patientInfo = is_numeric($this->psnId) ? PatientModel::get($this->psnId, '{debug: false}') : NULL;

		if (!$patientInfo) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'SORRY!!! No Patient information']);

		$isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;
		$isEdit = $this->patientInfo->RIGHT & _IS_EDITABLE;

		if (empty($this->action)) $this->action = 'home';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$groupId.' , Action = '.$this->action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.$this->_args[$argIndex]);
		// debugMsg($this->_args, '$args');

		if (preg_match('/^(visit|form|group)/', $this->action)) {
			$ret = R::Page(
				'imed.patient.'.$this->action,
				$patientInfo,
				$this->_args[$argIndex],
				$this->_args[$argIndex+1],
				$this->_args[$argIndex+2],
				$this->_args[$argIndex+3],
				$this->_args[$argIndex+4]
			);
		} else {
			$ret = R::Page(
				'imed.patient.'.$this->action,
				$self,
				$patientInfo,
				$this->_args[$argIndex],
				$this->_args[$argIndex+1],
				$this->_args[$argIndex+2],
				$this->_args[$argIndex+3],
				$this->_args[$argIndex+4]
			);
		}

		//debugMsg('TYPE = '.gettype());
		if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

		return $ret;
	}
}

/*
	$debug = true;

	function imed_patient($self, $psnId = NULL, $action = NULL, $tranId = NULL) {
		if (!is_numeric($psnId)) {$action = $psnId; unset($psnId);} // Action as psnId and clear

		if (empty($action) && empty($psnId)) return R::Page('imed.patient.home',$self);
		if (empty($action) && $psnId) {
			return R::Page('imed.patient.view',$self,$psnId);
		}


		$psnInfo = R::Model('imed.patient.get',$psnId);
		$psnId = $psnInfo->psnId;

		if (!$psnId) return message('error','No information');

		//if (!$isAccess) return message('error', 'Access Denied');

		$argIndex = 3; // Start argument

		//$ret .= 'PAGE IMED psnId = '.$psnId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex).'<br />';
		//$ret .= print_o(func_get_args(), '$args');

		$ret = R::Page(
			'imed.patient.'.$action,
			$self,
			$psnInfo,
			func_get_arg($argIndex),
			func_get_arg($argIndex+1),
			func_get_arg($argIndex+2),
			func_get_arg($argIndex+3),
			func_get_arg($argIndex+4)
		);
		if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

		//$ret .= 'Action = '.$action. ' Is create = '.($isCreatable ? 'YES' : 'NO').'<br />';
		//$ret .= print_o($psnInfo, '$psnInfo');

		return $ret;
	}
*/
?>