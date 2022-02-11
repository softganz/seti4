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

class ImedCareGiverInfoHome {
	var $userInfo;
	var $tranId;

	function __construct($userInfo = NULL, $tranId = NULL) {
		$this->userInfo = $userInfo;
	}

	function build() {
		if (!$this->userInfo->userId) return 'PROCESS ERROR';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Giver Info',
				'removeOnApp' => true,
			]),
			'body' => new Container([
				'children' => [
				], // children
			]), // Container
		]); // Scaffold
	}
}
?>