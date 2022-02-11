<?php
/**
* iMed :: App Patient Need List
* Created 2021-06-01
* Modify  2021-06-01
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.need
*/

import('widget:imed.patient.need');

class ImedAppInfoNeed {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ความต้องการของ '.$this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'child' => new ImedPatientNeedWidget(['patient' => $this->patientInfo, 'ref' => 'app']),
		]);
	}
}
?>