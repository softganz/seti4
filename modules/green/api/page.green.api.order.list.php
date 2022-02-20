<?php
/**
* Green API :: Order List
* Created 2022-02-17
* Modify  2022-02-17
*
* @return Object
*
* @usage green/api/order/list
*/

import('model:green.order.php');

class GreenApiOrderList extends Page {
	var $customerId;
	var $order;
	var $sort;
	var $item;

	function __construct() {
		$this->customerId = post('customerId');
		$this->order = post('order');
		$this->sort = post('sort');
		$this->item = post('item');
	}
	function build() {
		$result = (Object) [
			'count' => 0,
			'items' => [],
		];

		$conditions = [];
		$options = (Object) ['debug' => false];

		if ($this->customerId) $conditions['customerId'] = $this->customerId;

		$result = GreenOrderModel::items($conditions, $options);

		return $result;
	}
}
?>