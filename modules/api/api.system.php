<?php
/**
* System  :: System API
* Created :: 2022-10-14
* Modify  :: 2025-02-25
* Version :: 3
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage api/system/{action}[/{tranId}]
*/

use Softganz\DB;

class SystemApi extends PageApi {
	var $action;
	var $tranId;
	var $right;

	function __construct($action = NULL, $tranId = NULL) {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			'right' => (Object) [
				'admin' => is_admin(),
			],
		]);
	}

	private function _accessDenied() {
		return [
			'responseCode' => _HTTP_ERROR_FORBIDDEN,
			'text' => 'Access Denied',
		];
	}

	function date() {
		return date('Y-m-d H:i:s');
	}
}
?>