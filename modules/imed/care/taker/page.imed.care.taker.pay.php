<?php
/**
* iMed :: Care Taker Menu
* Created 2021-07-22
* Modify  2021-07-22
*
* @return Widget
*
* @usage imed/care/taker/0/eval
*/

$debug = true;

class ImedCareTakerPay {
	var $userInfo;
	var $tranId;

	function __construct($userInfo = NULL, $tranId = NULL) {
		$this->userInfo = $userInfo;
	}

	function build() {
		if (!$this->userInfo->userId) return 'PROCESS ERROR';

		return new Scaffold([
			'appBar' => new AppBar(['title' => 'ค่าบริการ']),
			'body' => new Container([
				'children' => [
					'รายการค่าบริการ',
				], // children
			]), // Container
		]); // Scaffold
	}
}
?>