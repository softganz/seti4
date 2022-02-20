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

class GreenApiRequirementDelete extends Page {
	var $requirementId;

	function __construct($requirementId) {
		$this->requirementId = $requirementId;
	}

	function build() {
		//TODO: ตรวจสอบ token ด้วย
echo 'AAAA';
		if (!$this->requirementId) {
			return (Object) [
				'code' => _HTTP_ERROR_BAD_REQUEST,
				'text' => 'ข้อมูลไม่ครบถ้วน',
			];
		}

		$result = (Object) [
			'id' => $this->requirementId,
		];

		// if ($this->requirementId) mydb::where('r.`requirementId` = :requirementId', ':requirementId', $this->requirementId);

		// $result = mydb::select(
		// 	'SELECT r.*
		// 	, pc.`name` `productName`
		// 	, pc.`unitType`
		// 	-- , uc.`name` `unitName`
		// 	FROM %green_requirement% r
		// 		LEFT JOIN %green_product_code% pc ON pc.`productId` = r.`productId`
		// 		-- LEFT JOIN %green_unit_code% uc ON uc.`unitId` = r.`units`
		// 	%WHERE%
		// 	LIMIT 1
		// 	'
		// );

		// $result = mydb::clearprop($result);
// debugMsg($result, '$result');
		// $result->items = $dbs->items;
		// $result->count = count($result->items);
		// $result->debug = mydb()->_query;

		// return $result;
	}
}
?>