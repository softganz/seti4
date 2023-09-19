<?php
/**
* Code    :: Code API
* Created :: 2023-09-18
* Modify  :: 2023-09-18
* Version :: 1
*
* @param String $action
* @return Array/Object
*
* @usage api/code/{action}
*/

use Softganz\DB;

class CodeApi extends PageApi {
	function __construct($action) {
		parent::__construct([
			'action' => $action,
		]);
	}

	function issue() {
		header('Access-Control-Allow-Origin: *');
		return DB::select(
			'SELECT
				`catId` `id`
				, `catParent` `parent`
				, `process`
				, `name`
			FROM %tag%
			WHERE `tagGroup` = "project:planning"
			ORDER BY `weight` ASC, `catId` ASC'
		);
	}
}
?>