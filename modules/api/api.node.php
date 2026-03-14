<?php
/**
 * Node    :: Node API
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2025-07-24
 * Modify  :: 2026-02-23
 * Version :: 2
 *
 * @param Int $nodeId
 * @param String $action
 * @param Int $tranId
 * @return Array/Object
 *
 * @usage api/node/{nodeId}/{action}[/{tranId}]
 */

use Softganz\DB;

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
	}

	function rightToBuild() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'Invalid Node ID');
		return true;
	}

	function members() {
		// Check multiple origin
		// $http_origin = $_SERVER['HTTP_ORIGIN'];
		// if (in_array($http_origin, array("http://localhost","http://hsmi.psu.ac.th"))) {
		// 	header("Access-Control-Allow-Origin: $http_origin");
		// }

		// header('SG-Access-Origin: '.$http_origin);
		
		if (is_admin()) {
			// Admin can access from anywhere
		} elseif (_HOST != _REFERER) {
			return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		}

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

	function memberAdd() {
		$addUserId = SG\getFirstInt(Request::all('userId'));
		$addMembership = SG\getFirst(Request::all('membership'), 'REGULAR MEMBER');

		if (empty($this->nodeId)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'Invalid Node ID');
		if (empty($addUserId)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'Invalid User ID');

		$user = UserModel::get($addUserId);

		if (empty($user)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'User Not Found');

		$nodeMember = NodeModel::members($this->nodeId)[i()->uid];

		$rightToAdd = is_admin() || in_array($nodeMember->orgMembership, ['ADMIN','MANAGER','OWNER','TRAINER']) || in_array($nodeMember->membership, ['ADMIN','MANAGER','OWNER','TRAINER']);

		if (!$rightToAdd) return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		DB::query([
			'INSERT INTO %topic_user%
			(`tpid`, `uid`, `membership`)
			VALUES
			(:nodeId, :userId, :membership)
			ON DUPLICATE KEY UPDATE
			`membership` = :membership;',
			'var' => [
				':nodeId' => $this->nodeId,
				':userId' => $user->userId,
				':membership' => strtoupper($addMembership)
			]
		]);

		LogModel::save([
			'module' => 'Node',
			'keyword' => 'Add member',
			'message' => $user->name.' ('.$user->userId.') was added to be a member of node '.$this->nodeId.' by '.i()->name.' ('.i()->uid.')',
			'keyId' => $this->nodeId,
		]);
		
		return apiSuccess('Member added successfully');
	}
}
?>