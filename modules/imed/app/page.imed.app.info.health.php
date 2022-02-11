<?php
/**
* iMed :: Patient Health Information
* Created 2021-06-01
* Modify  2021-06-01
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.health
*/

$debug = true;

class ImedAppInfoHealth {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อมูลสุขภาพ '.$this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				R::Page('imed.patient.health', NULL, $this->patientInfo),
			], // children
		]);
	}
}
?>