<?php
/**
* Module :: Description
* Created 2021-10-31
* Modify  2021-10-31
*
* @param Int $roomId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage /api/{id}/{action}[/{tranId}]
*/

$debug = true;

class RoomInfoApi extends Page {
	var $roomId;
	var $action;
	var $tranId;

	function __construct($roomInfo, $action, $tranId = NULL) {
		$this->roomId = $roomInfo->roomId;
		$this->action = $action;
		$this->tranId = $tranId;
		$this->roomInfo = $roomInfo;
	}

	function build() {
		debugMsg('roomId '.$this->roomId.' Action = '.$this->action.' TranId = '.$this->tranId);

		if (empty($this->roomId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $roomInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $roomInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'foo' :
				return 'Foo';
				break;

			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>