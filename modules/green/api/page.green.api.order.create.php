<?php
/**
* Module :: Description
* Created 2022-01-27
* Modify  2022-01-27
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class GreenApiOrderCreate extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		//TODO: ตรวจสอบ token ด้วย

		$data = (Object) [
			'orderId' => post('orderId') ? intval(post('orderId')) : NULL,
			'customerId' => intval(post('customerId')),
			'productId' => intval(post('productId')),
			'units' => intval(post('units')),
			'pricePerUnit' => floatval(post('pricePerUnit')),
			'start' => post('start'),
			'end' => post('end'),
			'status' => '',
			'parentOrderId' => post('parentOrderId'),
			'created' => date('U'),
		];

		if (empty($data->customerId) || empty($data->productId)) {
			return (Object) [
				'code' => _HTTP_ERROR_NOT_ACCEPTABLE,
				'text' => 'ข้อมูลไม่ครบถ้วน',
			];
			return new ErrorMessage(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลไม่ครบถ้วน']);
		}

		mydb::query(
			'INSERT INTO %green_order%
			(`orderId`, `parentOrderId`, `customerId`, `productId`, `units`, `pricePerUnit`, `start`, `end`, `created`)
			VALUES
			(:orderId, :parentOrderId, :customerId, :productId, :units, :pricePerUnit, :start, :end, :created)
			ON DUPLICATE KEY UPDATE
			`units` = :units
			, `pricePerUnit` = :pricePerUnit
			, `start` = :start
			, `end` = :end
			',
			$data
		);

		if (!$data->orderId) $data->orderId = mydb()->insert_id;
		// debugMsg(mydb()->_query);

		return $data;

		debugMsg(mydb()->_query);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Title',
			]), // AppBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}
}
?>