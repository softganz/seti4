<?php
/**
* Model :: Description
* Created 2022-02-17
* Modify 	2022-02-17
*
* @param Array $args
* @return Object
*
* @usage new GreenRequirementModel([])
* @usage GreenRequirementModel::function($conditions, $options)
*/

class GreenRequirementModel {
	function __construct($args = []) {
	}

	public static function get($requirementId, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'requirementId' => NULL,
			'info' => (Object) [],
			'right' => (Object) [
				'edit' => false,
			],
		];

		if ($requirementId) mydb::where('r.`requirementId` = :requirementId', ':requirementId', $requirementId);

		$result->info = mydb::select(
			'SELECT r.*
			, pc.`name` `productName`
			, pc.`unitType`
			-- , uc.`name` `unitName`
			FROM %green_requirement% r
				LEFT JOIN %green_product_code% pc ON pc.`productId` = r.`productId`
				-- LEFT JOIN %green_unit_code% uc ON uc.`unitId` = r.`units`
			%WHERE%
			LIMIT 1
			'
		);

		if (!$result->info->requirementId) return (Object) ['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูล'];

		$result->requirementId = $result->info->requirementId;
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

		$result = (Object) [];

		return $result;
	}

	public static function delete($requirementId) {
		$result = (Object) ['code' => NULL, 'query' => ''];

		mydb::query(
			'DELETE FROM %green_requirement%
			WHERE `requirementId` = :requirementId
			LIMIT 1',
			[':requirementId' => $requirementId]
		);

		$result->code = _HTTP_OK;
		$result->query = mydb()->_query;
		return $result;
	}
}
?>