<?php
/**
* Green API :: Add Order Interest
* Created 2022-02-17
* Modify  2022-02-17
*
* @return Object
*
* @usage green/api/order/interest/new
*/

import('model:green.order.php');

class GreenApiOrderInterestNew extends Page {
	var $orderId;
	var $pricePerUnit;

	function __construct() {
		$this->orderId = intval(post('orderId'));
		$this->pricePerUnit = floatval(post('pricePerUnit'));
	}
	function build() {
		$data = (Object) [
			'orderId' => $this->orderId,
			'sellerId' => i()->uid,
			'pricePerUnit' => $this->pricePerUnit,
		];

		if (empty($data->orderId) || empty($data->sellerId) || empty($data->pricePerUnit)) {
			return ['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลไม่ครบถ้วน'];
		}

		$orderInfo = GreenOrderModel::get($data->orderId);

		if ($orderInfo->code) return $orderInfo;

		$sellerInterest = GreenOrderModel::addInterest($data);
		// debugMsg($sellerInterest, '$sellerInterest');
		$result = (Object) [

		];

		return $sellerInterest;
	}
}
?>