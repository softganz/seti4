<?php
/**
* Green :: Order API
* Created 2022-02-17
* Modify  2022-02-17
*
* @param Object $orderId
* @param String $action
* @return Object
*
* @usage green/api/order/{id}[/{action}]
*/

import('model:green.order.php');

class GreenApiOrder extends Page {
	var $orderId;
	var $action;
	var $tranId;
	var $orderInfo;

	function __construct($orderId, $action = NULL, $tranId = NULL) {
		$this->orderInfo = GreenOrderModel::get($orderId);
		$this->orderId = $this->orderInfo->orderId;
		$this->action = $action;
		$this->tranId = $tranId;
		// debugMsg($this->orderInfo, '$orderInfo');
	}

	function build() {
		//TODO: ตรวจสอบ token ด้วย

		if ($this->orderInfo->code) {
			return $this->orderInfo;
		}

		switch ($this->action) {
			case 'delete':
				if ($this->orderInfo->right->edit) {
					GreenOrderModel::delete($this->orderId);
					$result = (Object) ['code' => _HTTP_OK, 'text' => 'Requirement Deleted'];
				} else {
					$result = (Object) ['code' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied'];
				}
				break;

			case 'interest':
				$result = GreenOrderModel::getOrderInterest($this->orderId);

				// if ($orderInterestList->code) return $orderInfo;

				// $orderInterestList->uid = i()->uid;

				break;

			case 'interest.new':
				$data = (Object) [
					'orderId' => intval(SG\getFirst($this->orderId, post('orderId'))),
					'sellerId' => intval(post('sellerId')),
					'unit' => intval(post('unit')),
					'pricePerUnit' => floatval(post('pricePerUnit')),
				];

				if (empty($data->orderId) || empty($data->sellerId) || empty($data->pricePerUnit)) {
					return ['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลไม่ครบถ้วน'];
				}

				$result = GreenOrderModel::addInterest($data);

				break;

			case 'assign':
				$data = (Object) [
					'orderId' => intval(SG\getFirst($this->orderId, post('orderId'))),
					'sellerId' => intval(post('sellerId')),
					'unit' => intval(post('unit')),
					'pricePerUnit' => floatval(post('pricePerUnit')),
				];

				if (empty($data->orderId) || empty($data->sellerId) || empty($data->unit) || empty($data->pricePerUnit)) {
					return ['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลไม่ครบถ้วน'];
				}

				$result = GreenOrderModel::assignSeller($data);

				break;

			case 'assign.list':
				$result = GreenOrderModel::assignItems($this->orderId);
				break;

			default:
				$result = empty($this->action) ? $this->orderInfo->info : ['code' => _HTTP_ERROR_NOT_FOUND, 'text' => 'Page not found'];
				break;
		}

		return $result;
	}
}
?>