<?php
/**
* iMed :: Patient Elder Information
* Created 2021-06-01
* Modify  2021-06-01
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.elder
*/

$debug = true;

class ImedAppInfoElder {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อมูลผู้สูงอายุ '.$this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				R::Page('imed.patient.elder', NULL, $this->patientInfo),
				'<style type="text/css">
				.page.-main>.header {display: none;}
				</style>'
			], // children
		]);
	}
}
?>