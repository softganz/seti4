<?php
/**
* Green API :: AdList Order Interest
* Created 2022-02-17
* Modify  2022-02-17
*
* @return Object
*
* @usage green/api/order/interest
*/

import('model:green.order.php');

class GreenApiOrderInterest extends Page {
	var $orderId;

	function __construct($orderId = NULL) {
		$this->orderId = intval(SG\getFirst($orderId,post('orderId')));
	}

	function build() {
		if (empty($this->orderId)) {
			return ['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลไม่ครบถ้วน'];
		}

		$orderInterestList = GreenOrderModel::getOrderInterest($this->orderId);

		if ($orderInterestList->code) return $orderInfo;

		$orderInterestList->uid = i()->uid;
		return $orderInterestList;
	}
}
?>