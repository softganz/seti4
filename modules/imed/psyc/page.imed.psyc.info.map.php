<?php
/**
* iMed :: Patient Map
* Created 2021-05-27
* Modify  2021-05-31
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/psyc/{id}/info.map
*/

$debug = true;

class ImedPsycInfoMap {
	var $patientInfo;
	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {

		return R::Page('imed.patient.map', NULL, $this->patientInfo);
	}
}
?>