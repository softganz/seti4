<?php
/**
* iMed :: Care Giver Do
* Created 2021-07-22
* Modify  2021-07-22
*
* @return Widget
*
* @usage imed/care/giver/0/do
*/

$debug = true;

class ImedCareGiverDo {
	var $userInfo;
	var $tranId;

	function __construct($userInfo = NULL, $tranId = NULL) {
		$this->userInfo = $userInfo;
	}

	function build() {
		if (!$this->userInfo->userId) return 'PROCESS ERROR';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'บันทึกการให้บริการ',
				'removeOnApp' => true,
			]),
			'body' => new Container([
				'children' => [
					'รายการบันทึกการให้บริการ',
				], // children
			]), // Container
		]); // Scaffold
	}
}
?>