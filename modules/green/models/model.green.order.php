<?php
/**
* Green Model :: Order
* Created 2022-02-17
* Modify 	2022-02-18
*
* @return Object
*
* @usage new GreenOrderModel([])
* @usage GreenOrderModel::function($conditions, $options)
*/

class GreenOrderModel {
	public static function get($orderId, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'orderId' => NULL,
			'info' => (Object) [],
			'right' => (Object) [
				'edit' => false,
			],
		];

		if ($orderId) mydb::where('o.`orderId` = :orderId', ':orderId', $orderId);

		$result->info = mydb::select(
			'SELECT
			o.`orderId`
			, o.`parentOrderId`
			, o.`uid` `userId`
			, u.`name` `ownerName`
			, o.`customerId`
			, customer.`name` `customerName`
			, o.`productId`
			, pcode.`name` `productName`
			, o.`units`
			, pcode.`unitType`
			, o.`start`
			, o.`end`
			, o.`status`
			, FROM_UNIXTIME(o.`created`) `createdDate`
			FROM %green_order% o
				LEFT JOIN %db_org% customer ON customer.`orgId` = o.`customerId`
				LEFT JOIN %users% u ON u.`uid` = o.`uid`
				LEFT JOIN %green_product_code% pcode ON pcode.`productId` = o.`productId`
			%WHERE%
			LIMIT 1
			'
		);

		if (!$result->info->orderId) return (Object) ['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลคำสั่งซื้อหมายเลข '.$orderId];

		$result->orderId = $result->info->orderId;
		$result->info = mydb::clearprop($result->info);

		$isAdmin = is_admin('green');
		$result->right->edit = $result->info->customerId == i()->uid || $isAdmin;

		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}


		$result = (Object) [
			'count' => 0,
			'items' => [],
			'debug' => [],
		];

		if ($debug) $result->debug['conditions'] = $conditions;

		if ($conditions->customerId) mydb::where('o.`customerId` = :customerId', ':customerId', $conditions->customerId);

		$result->items = mydb::select(
			'SELECT
			o.`orderId`
			, o.`parentOrderId`
			, o.`uid` `userId`
			, u.`name` `ownerName`
			, o.`customerId`
			, customer.`name` `customerName`
			, o.`productId`
			, pcode.`name` `productName`
			, o.`units`
			, pcode.`unitType`
			, o.`start`
			, o.`end`
			, o.`status`
			, FROM_UNIXTIME(o.`created`) `createdDate`
			FROM %green_order% o
				LEFT JOIN %db_org% customer ON customer.`orgId` = o.`customerId`
				LEFT JOIN %users% u ON u.`uid` = o.`uid`
				LEFT JOIN %green_product_code% pcode ON pcode.`productId` = o.`productId`
			%WHERE%
			ORDER BY o.`orderId` DESC
			'
		)->items;

		if ($debug) $result->debug[] = mydb()->_query;

		if (!$debug) unset($result->debug);
		$result->count = count($result->items);

		return $result;
	}

	public static function create($data) {
		$result = (Object) [
			'orderId' => NULL,
			'data' => $data,
		];

		return $result;
	}

	public static function delete($orderId) {
		$result = (Object) ['code' => NULL, 'query' => ''];

		mydb::query(
			'DELETE FROM %green_order%
			WHERE `orderId` = :orderId
			LIMIT 1',
			[':orderId' => $orderId]
		);

		$result->code = _HTTP_OK;
		$result->query = mydb()->_query;
		return $result;
	}

	public static function addInterest($data) {
		$data->userId = i()->uid;
		$data->created = date('U');
		mydb::query(
			'INSERT INTO %green_interest%
			(`orderId`, `sellerId`, `uid`, `unit`, `pricePerUnit`, `created`)
			VALUES
			(:orderId, :sellerId, :userId, :unit, :pricePerUnit, :created)
			ON DUPLICATE KEY UPDATE
			`unit` = :unit
			, `pricePerUnit` = :pricePerUnit
			',
			$data
		);

		$result = (Object) [
			'orderId' => $data->orderId,
			'sellerId' => $data->sellerId,
			'userId' => $data->userId,
			'unit' => $data->unit,
			'pricePerUnit' => $data->pricePerUnit,
		];
		return $result;
	}

	public static function getOrderInterest($orderId) {
		$result = (Object) [
			'orderId' => $orderId,
			'count' => 0,
			'items' => [],
		];

		mydb::where('i.`orderId` = :orderId', ':orderId', $orderId);

		$result->items = mydb::select(
			'SELECT
			i.`orderId`
			, i.`sellerId`
			, o.`name` `sellerName`
			, i.`uid` `userId`
			, u.`name` `ownerName`
			, od.`productId`
			, pcode.`name` `productName`
			, pcode.`unitType`
			, i.`unit`
			, i.`pricePerUnit`
			, FROM_UNIXTIME(i.`created`) `createdDate`
			FROM %green_interest% i
				LEFT JOIN %green_order% od ON od.`orderId` = i.`orderId`
				LEFT JOIN %db_org% o ON o.`orgId` = i.`sellerId`
				LEFT JOIN %users% u ON u.`uid` = i.`sellerId`
				LEFT JOIN %green_product_code% pcode ON pcode.`productId` = od.`productId`
			%WHERE%
			'
		)->items;

		$result->count = count($result->items);
		return $result;
	}

	public static function assignSeller($data) {
		$data->uid = i()->uid;
		$data->created = date('U');
		mydb::query(
			'INSERT INTO %green_assign%
			(`orderId`, `sellerId`, `uid`, `unit`, `pricePerUnit`, `created`)
			VALUES
			(:orderId, :sellerId, :uid, :unit, :pricePerUnit, :created)
			ON DUPLICATE KEY UPDATE
			`unit` = :unit
			, `pricePerUnit` = :pricePerUnit
			',
			$data
		);

		$result = (Object) [
			'orderId' => $data->orderId,
			'sellerId' => $data->sellerId,
			'unit' => $data->unit,
			'pricePerUnit' => $data->pricePerUnit,
		];
		return $result;
	}

	public static function assignItems($orderId) {
		$result = (Object) [
			'orderId' => $orderId,
			'count' => 0,
			'items' => [],
		];

		mydb::where('a.`orderId` = :orderId', ':orderId', $orderId);

		$result->items = mydb::select(
			'SELECT
			a.`orderId`
			, a.`sellerId`
			, s.`name` `sellerName`
			, a.`userId`
			, u.`name` `ownerName`
			, a.`unit`
			, a.`pricePerUnit`
			, FROM_UNIXTIME(a.`created`) `createdDate`
			FROM %green_assign% a
				LEFT JOIN %db_org% s ON s.`orgId` = a.`sellerId`
				LEFT JOIN %users% u ON u.`uid` = a.`userId`
			%WHERE%
			'
		)->items;

		$result->count = count($result->items);
		return $result;
	}

}
?>