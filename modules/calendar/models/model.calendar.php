<?php
/**
* Calendar:: Calendar Model
* Created :: 2023-01-15
* Modify  :: 2023-01-15
* Version :: 1
*
* @param Array $args
* @return Object
*
* @usage new CalendarModel([])
* @usage CalendarModel::function($conditions, $options)
*/

class CalendarModel {
	function __construct($args = []) {
	}

	public static function get($id, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = mydb::select(
			'SELECT `id` `calId`, `orgId`, c.* FROM %calendar% c WHERE `id` = :calId LIMIT 1;
			-- {fieldOnly: true}',
			[':calId' => $id]
		);

		return $result;
	}

	// public static function items($conditions, $options = '{}') {
	// 	$defaults = '{debug: false}';
	// 	$options = SG\json_decode($options, $defaults);
	// 	$debug = $options->debug;

	// 	if (is_string($conditions) && preg_match('/^{/',$conditions)) {
	// 		$conditions = SG\json_decode($conditions);
	// 	} else if (is_object($conditions)) {
	// 		//
	// 	} else if (is_array($conditions)) {
	// 		$conditions = (Object) $conditions;
	// 	} else {
	// 		$conditions = (Object) ['id' => $conditions];
	// 	}

	// 	$result = (Object) [];

	// 	return $result;
	// }
}
?>