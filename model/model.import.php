<?php
/**
* Model   :: ImportModel File
* Created :: 2026-05-20
* Modify  :: 2026-05-20
* Version :: 1
*
* @param Array $args
* @return Object
*
* @usage import('model:import.php')
* @usage new ImportModel([])
* @usage ImportModel::function($conditions)
*/

import('package:external/shuchkin/SimpleXLSX.php'); // https://github.com/shuchkin/simplexlsx

use Shuchkin\SimpleXLSX;

class ImportModel {
	static function xlsx($args = []) {
		$args = (Object) array_merge(
			[
				'fileName' => null, // String
				'debug' => false, // boolean
			],
			(Array) $args
		);

		if (empty($args->fileName)) return [];

		// $inputFile = 'upload/students.xlsx';

		if ($xlsx = SimpleXLSX::parse($args->fileName)) {
			return $xlsx;
		} else {
			throw new Exception(SimpleXLSX::parseError(), _HTTP_ERROR_NOT_FOUND);
		}
	}
}
?>
