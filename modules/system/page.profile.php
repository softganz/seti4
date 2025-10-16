<?php
/**
* Profile :: Profile Page Controller
* Created :: 2024-07-23
* Modify  :: 2024-07-23
* Version :: 1
*
* @param Int $userId
* @param String $action
* @return Widget
*
* @usage profile[/{userId}/{action}/{tranId}]
*/

class Profile extends PageController {
	var $userId;
	var $action;
	var $info;

	function __construct($userId = NULL, $action = NULL) {
		if (empty($userId) && empty($action)) $action = 'home';
		else if ($userId && empty($action)) $action = 'view';
		parent::__construct([
			'userId' => $userId = SG\getFirstInt($userId),
			'action' => 'profile.'.$action,
			'args' => func_get_args(),
			'info' => is_numeric($userId) ? UserModel::get($userId) : NULL,
		]);
	}
}
?>