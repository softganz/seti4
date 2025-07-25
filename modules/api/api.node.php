<?php
/**
* Node    :: Node API
* Created :: 2025-07-24
* Modify  :: 2025-07-24
* Version :: 1
*
* @param Int $nodeId
* @param String $action
* @param Int $tranId
* @return Array/Object
*
* @usage api/node/{nodeId}/{action}[/{tranId}]
*/

class NodeApi extends PageApi {
	var $nodeId;
	var $action;
	var $tranId;

	function __construct($nodeId = NULL, $action = NULL, $tranId = NULL) {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			'nodeInfo' => $nodeInfo = (is_numeric($nodeId) ? \NodeModel::get($nodeId) : NULL),
			'nodeId' => $nodeInfo->nodeId,
		]);
		// debugMsg($this, '$this');
	}

	function rightToBuild() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'PROCESS ERROR');
		return true;
	}

	function build() {

		return parent::build();
	}

	function members() {
		// Check multiple origin
		// $http_origin = $_SERVER['HTTP_ORIGIN'];
		// if (in_array($http_origin, array("http://localhost","http://hsmi.psu.ac.th"))) {
		// 	header("Access-Control-Allow-Origin: $http_origin");
		// }

		// header('SG-Access-Origin: '.$http_origin);
		if (_HOST != _REFERER) return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		$result = [];

		foreach (\NodeModel::members($this->nodeId) as $member) {
			$result[] = (Object) [
				'id' => $member->uid,
				'username' => $member->username,
				'fullname' => $member->name,
				'membership' => $member->membership,
			];
		}
		return $result;
	}

	function memberCheck() {
		$isMember = false;

		if (!i()->ok) return (Object) ['member' => false];

		foreach (\NodeModel::members($this->nodeId) as $member) {
			if ($member->uid == i()->uid) {
				$isMember = true;
				break; // Found current user
			}
		}
		return (Object) ['member' => $isMember];
	}
}
?>