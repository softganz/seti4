<?php
/**
* iMed :: Patient Disabled Information
* Created 2021-06-01
* Modify  2021-06-01
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.disabled
*/

$debug = true;

class ImedAppInfoDisabled {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อมูลคนพิการ '.$this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				R::Page('imed.patient.disabled', NULL, $this->patientInfo),
				'<style type="text/css">
				#imed-care-disabled>.header {display: none;}
				</style>'
			], // children
		]);
	}
}
?>