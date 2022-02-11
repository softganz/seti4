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

import('package:imed/care/models/model.service.code.php');

class ServiceMenuModel extends ServiceCodeModel {
	// // @override
	// public static function get($conditions, $options = '{}') {
	// 	return parent::get($conditions);
	// }

	// @override
	public static function items($conditions = [], $option = []) {
		$defaults = '{debug: false, order: "cs.`servId`"}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$conditions['menu'] = true;

		$result = parent::items($conditions, $options);

		return $result;
	}
}
?>