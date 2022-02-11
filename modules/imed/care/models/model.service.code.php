<?php
/**
* Model :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("module.method", $conditions, $options)
*/

$debug = true;

class ServiceCodeModel {
	static function get($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = NULL;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		$result = mydb::select(
			'SELECT `servId` `serviceId`, `name`, `detail`, `description`, CAST(`unitprice` AS DOUBLE) `unitprice`, `icon`
			FROM %imed_code_serv%
			WHERE `servId` = :serviceId
			LIMIT 1',
			':serviceId', $conditions->id
		);

		mydb::clearprop($result);

		return $result;
	}

	static function items($conditions = [], $option = []) {
		$defaults = '{debug: false, order: "cs.`servId`"}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if ($conditions['package']) mydb::where('cs.`package` = 1');
		if ($conditions['menu']) mydb::where('cs.`package` IS NULL');
		mydb::value('$ORDER$', 'ORDER BY '.$options->order);

		$result = mydb::select('SELECT
			cs.`servId` `serviceId`
			, cs.`name`
			, cs.`detail`
			, cs.`description`
			, CAST(cs.`unitprice` AS DOUBLE) `unitprice`
			, cs.`icon`
			FROM %imed_code_serv% cs
			%WHERE%
			$ORDER$
			'
		)->items;

		return $result;
	}
}
?>