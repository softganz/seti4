<?php
/**
* Imed :: View Visit Item
* Created 2021-09-04
* Modify  2021-09-04
*
* @param Object $patientInfo
* @param Int $seqId
* @return Widget
*
* @usage imed/visit/{id}/item/{seqId}
*/

$debug = true;

import('widget:imed.visit.item');

class ImedVisitItem extends Page {
	var $psnId;
	var $seqId;
	var $refApp;
	var $patientInfo;
	var $visitInfo;

	function __construct($patientInfo, $visitInfo) {
		$this->psnId = $patientInfo->psnId;
		$this->seqId = $seqId;
		$this->patientInfo = $patientInfo;
		$this->visitInfo = $visitInfo;
		$this->refApp = post('ref');
		// debugMsg(post(),'post()');
	}

	function build() {
		return new Widget([
			'child' => new ImedVisitItemWidget([
				'child' => $this->visitInfo,
				'refApp' => $this->refApp,
			]),
		]);
	}
}
?>