<?php
/**
* Module :: Description
* Created 2021-11-20
* Modify  2021-11-20
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class ApiAddressSplit extends Page {
	var $address;
	var $type;

	function __construct() {
		$this->address = post('address');
		$this->type = SG\getFirst(post('type'), 'long');
	}

	function build() {
		if (empty($this->address)) return ['usage' => 'api/address?address=00/00 ซ.ชื่อซอย ถ.ชื่อถนน ม.0 ต.ชื่อตำบล อ.ชื่ออำเภอ จ.ชื่อจังหวัด'];

		$result = (Object) [
			'newAddress' => '',
			'areaCode' => '',
			'address' => SG\explode_address($this->address),
			'src' => [
				'address' => $this->address,
				'type' => $this->type,
			],
		];
		$result->newAddress = SG\implode_address($result->address, $this->type);
		$result->areaCode = $result->address['areaCode'];
		// debugMsg($result, '$result');
		return $result;
	}
}
?>