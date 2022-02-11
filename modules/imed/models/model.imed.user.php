<?php
/**
* iMed Model :: User Information
* Created 2021-07-22
* Modify  2021-07-22
*
* @return Object
*
* @usage new ImedUserModel([userId,role])
*/

$debug = true;

import('model:user.php');

class ImedUserModel extends UserModel {
	var $userId;
	var $role;

	function __construct($args = []) {
		parent::__construct($args['userId']);
		if ($args['role']) $this->role = $this->_roleInfo($args['role']);
		// debugMsg($this, '$this');
	}

	public static function register($data = []) {
		$result = (Object) [
			'data' => $data,
			'_query' => [],
		];

		$stmt = 'UPDATE %users% SET
			`real_name` = :realName
			, `last_name` = :lastName
			, `phone` = :phone
			WHERE `uid` = :userId LIMIT 1';
		mydb::query($stmt, $data);
		$result->_query[] = mydb()->_query;

		$stmt = 'INSERT INTO %users_role%
		(`uid`, `role`, `status`, `created`)
		VALUES
		(:userId, :role, :status, :created)
		ON DUPLICATE KEY UPDATE
		`uid` = :userId
		';

		mydb::query($stmt, $data);
		$result->_query[] = mydb()->_query;
		return $result;
	}

	function _roleInfo($role) {
		$rs = mydb::select(
			'SELECT * FROM %users_role% WHERE `uid` = :userId AND `role` = :role LIMIT 1',
			':userId', $this->userId,
			':role', $role
		);
		mydb::clearprop($rs);
		return $rs;
	}

	function isRole() {
		return $this->role->role;
	}

	function isTaker() {}

	function isGiver() {}

	function isEnable() {
		return $this->role->status === 'ENABLE';
	}

	function isWaiting() {
		return $this->role->status === 'WAITING';
	}

	function isBlock() {
		return $this->role->status === 'BLOCK';
	}

	public static function takers() {
		return mydb::select(
			'SELECT r.*, u.`username`, u.`name`, u.`datein`
			FROM %users_role% r
				LEFT JOIN %users% u USING(`uid`)
			WHERE `role` = :role',
			':role', 'IMED TAKER'
		)->items;
	}

	public static function givers() {
		return mydb::select(
			'SELECT r.*, u.`username`, u.`name`, CONCAT(u.`real_name`, " ", u.`last_name`) `realName`, u.`datein`
			FROM %users_role% r
				LEFT JOIN %users% u USING(`uid`)
			WHERE `role` = :role',
			':role', 'IMED GIVER'
		)->items;
	}

}
?>