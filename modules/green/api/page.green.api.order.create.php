<?php
/**
* Green API :: Create Order
* Created 2022-01-27
* Modify  2022-02-17
*
* @return Widget
*
* @usage green/api/order/create
*/

class GreenApiOrderCreate extends Page {
	function build() {
		//TODO: ตรวจสอบ token ด้วย

		$data = (Object) [
			'orderId' => post('orderId') ? intval(post('orderId')) : NULL,
			'userId' => i()->uid,
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
			// return new ErrorMessage(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลไม่ครบถ้วน']);
		}

		mydb::query(
			'INSERT INTO %green_order%
			(`orderId`, `parentOrderId`, `uid`, `customerId`, `productId`, `units`, `pricePerUnit`, `start`, `end`, `created`)
			VALUES
			(:orderId, :parentOrderId, :userId, :customerId, :productId, :units, :pricePerUnit, :start, :end, :created)
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
	}
}
?>