<?php
/**
* iMed :: Patient Map Information
* Created 2021-06-01
* Modify  2021-06-01
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.map
*/

$debug = true;

class ImedAppInfoMap {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		$isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;
		if (!$isAccess) return message('error', $this->patientInfo->error);

		return new Scaffold([
			'children' => [
				R::Page('imed.patient.map', NULL, $this->patientInfo),
			], // children
		]);
	}
}
?>