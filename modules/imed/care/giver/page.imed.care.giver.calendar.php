<?php
/**
* iMed :: Care Giver Calendar
* Created 2021-07-22
* Modify  2021-07-22
*
* @return Widget
*
* @usage imed/care/giver/0/calendar
*/

$debug = true;

class ImedCareGiverCalendar {
	var $userInfo;
	var $tranId;

	function __construct($userInfo = NULL, $tranId = NULL) {
		$this->userInfo = $userInfo;
	}

	function build() {
		if (!$this->userInfo->userId) return 'PROCESS ERROR';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'นัดหมายบริการ',
				'removeOnApp' => true,
			]),
			'body' => new Container([
				'children' => [
					'รายการนัดหมายบริการ',
				], // children
			]), // Container
		]); // Scaffold
	}
}
?>