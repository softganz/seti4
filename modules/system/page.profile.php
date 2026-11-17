<?php
/**
 * Profile  :: Profile Page Controller
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2024-07-23
 * Modified :: 2026-11-17
 * Version  :: 4
 *
 * @param int $userId
 * @param string $action
 *
 * @uses profile[/{userId}/{action}/{tranId}]
 */

class Profile extends PageController {
	public $userId;

	function __construct($userId = null, $action = null) {
		if (empty($userId) && empty($action)) {
			$action = 'home';
		} elseif ($userId && empty($action)) {
			$action = 'view';
		}
		parent::__construct([
			'userId' => ($userId = SG\getFirstInt($userId)),
			'action' => 'profile.' . $action,
			'args' => func_get_args(),
			'info' => is_numeric($userId) ? \UserModel::get($userId) : null
		]);
	}
}
?>
