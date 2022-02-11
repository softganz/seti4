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

class GreenApiProductList extends Page {
	var $customerId;

	function __construct($arg1 = NULL) {
		$this->customerId = post('customerId');
		$this->order = SG\getFirst(post('order'), 'id');
		$this->sort = SG\getFirst(post('sort'), 'd');
		$this->page = SG\getFirst(post('page'), 1);
		$this->item = SG\getFirst(post('item'), 10);
	}

	function build() {
		//TODO: ตรวจสอบ token ด้วย

		$orderList = ['id' => 'requirementId', 'start' => 'start'];
		$sortList = ['a' => 'ASC', 'd' => 'DESC'];

		// if (!$this->customerId) {
		// 	return (Object) [
		// 		'code' => _HTTP_ERROR_BAD_REQUEST,
		// 		'text' => 'ข้อมูลไม่ครบถ้วน',
		// 	];
		// }

		// $data = (Object) [
		// 	'requirementId' => post('requirementId') ? intval(post('requirementId')) : NULL,
		// 	'customerId' => intval(post('customerId')),
		// 	'productId' => intval(post('productId')),
		// 	'units' => intval(post('units')),
		// 	'pricePerUnit' => floatval(post('pricePerUnit')),
		// 	'start' => post('start'),
		// 	'end' => post('end'),
		// 	'created' => date('U'),
		// ];

		$result = (Object) [
			'count' => 0,
			'items' => []
		];

		// if ($this->customerId) mydb::where('r.`customerId` = :customerId', ':customerId', $this->customerId);

		// mydb::value('$ORDER$', 'ORDER BY '.$orderList[$this->order].' '.$sortList[$this->sort]);
		// mydb::value('$LIMIT$', 'LIMIT '.($this->page - 1).','.$this->item);

		$dbs = mydb::select(
			'SELECT p.*
			FROM %green_product_code% p
			ORDER BY CONVERT(p.`name` USING tis620) ASC
			'
		);

		$result->items = $dbs->items;
		$result->count = count($result->items);
		// $result->debug = mydb()->_query;

		return $result;

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