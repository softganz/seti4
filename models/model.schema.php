<?php
/**
* Schema :: Schema Model
* Created 2022-09-23
* Modify 	2022-09-23
*
* @param Array $args
* @return Object
*
* @usage new SchemaModel([])
* @usage SchemaModel::function($conditions, $options)
*/

class SchemaModel {
	var $schemaName;

	function __construct($schemaName) {
		$this->schemaName = $schemaName;
		if ($schemaName) {
			foreach(SchemaModel::get($schemaName) as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}

	public static function get($schemaName) {
		return json_decode(R::Asset($schemaName));
	}

	public function indicator($section) {
		foreach ($this->body as $metrix) {
			foreach ($metrix->items as $metrinItem) {
				foreach ($metrinItem->indicator as $indicator) {
					// debugMsg($indicator, '$indicator');
					if ($indicator->section == $section) return $indicator;
				}
			}
		}
		return [];
	}
}
?>