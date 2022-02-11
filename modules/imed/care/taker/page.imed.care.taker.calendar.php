<?php
/**
* iMed :: Care Taker Menu
* Created 2021-07-22
* Modify  2021-07-22
*
* @return Widget
*
* @usage imed/care/taker/0/menu
*/

$debug = true;

class ImedCareTakerCalendar {
	var $userInfo;
	var $tranId;

	function __construct($userInfo = NULL, $tranId = NULL) {
		$this->userInfo = $userInfo;
	}

	function build() {
		if (!$this->userInfo->userId) return 'PROCESS ERROR';

		return new Scaffold([
			'appBar' => new AppBar(['title' => 'นัดหมายบริการ']),
			'body' => new Container([
				'children' => [
					'รายการนัดหมายบริการ',
				], // children
			]), // Container
		]); // Scaffold
	}
}
?>