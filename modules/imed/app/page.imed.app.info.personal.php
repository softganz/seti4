<?php
/**
* iMed :: Patient Personal Information
* Created 2021-06-01
* Modify  2021-06-01
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.personal
*/

$debug = true;

class ImedAppInfoPersonal {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				R::Page('imed.patient.individual', NULL, $this->patientInfo),
				'<style type="text/css">
				#patient-info-photo {display: none;}
				.imed-care-individual>.header {display: none;}
				table#imed-patient-individual>tbody>tr>td:first-child {width: 100px;}
				</style>'
			], // children
		]);
	}
}
?>