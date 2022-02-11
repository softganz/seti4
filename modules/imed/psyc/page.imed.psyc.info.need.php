<?php
/**
* iMed :: Patient Need List
* Created 2021-06-01
* Modify  2021-06-11
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/psyc/{id}/info.need
*/

import('widget:imed.patient.need');

class ImedPsycInfoNeed {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ความต้องการ - '.$this->patientInfo->fullname,
				'removeOnApp' => true,
			]), // AppBar
			'child' => new ImedPatientNeedWidget(['patient' => $this->patientInfo, 'ref' => 'psyc']),
		]);
	}
}
?>