<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

class ImedAppInfoPo {
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
				R::Page('imed.patient.po', NULL, $this->patientInfo),
			], // children
		]);
	}
}
?>