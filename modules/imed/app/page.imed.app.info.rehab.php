<?php
/**
* iMed :: Patient Rehab Information
* Created 2021-06-01
* Modify  2021-06-01
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.rehab
*/

$debug = true;

class ImedAppInfoRehab {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อมูลผู้ป่วยรอการฟื้นฟู '.$this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				R::Page('imed.patient.rehab', NULL, $this->patientInfo),
				'<style type="text/css">
				.page.-main>.header {display: none;}
				</style>'
			], // children
		]);
	}
}
?>