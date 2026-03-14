<?php
/**
 * API     :: User API
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-11-19
 * Modify  :: 2026-02-23
 * Version :: 3
 *
 * @return Array
 *
 * @usage api/user
 */

class UserApi extends PageApi {
	var $queryText;
	var $username;
	var $email;
	var $status = 'enable';
	var $page = 1;
	var $item = 10;

	function __construct() {
		parent::__construct([
			'queryText' => SG\getFirst(Request::all('q')),
			'username' => Request::all('username'),
			'email' => Request::all('email'),
			'status' => SG\getFirst(Request::all('status'), $this->status),
			'page' => SG\getFirst(Request::all('page'), Request::all('p'), $this->page),
			'item' => SG\getFirst(Request::all('item'), Request::all('n'), $this->item),
		]);
	}

	function build() {
		$result = [];

		// Only referer form same site except admin, to prevent abuse of this API to query user information without permission.
		if (is_admin()) {
			// Admin can query without referer
		} else if (
			(empty($this->queryText) && empty($this->username) && empty($this->email))
			|| !i()->ok || _HOST != _REFERER
		) {
			return $result;
		}

		$isAdmin = i()->admin;

		$users = UserModel::getUsers([
			'queryText' => $this->queryText,
			'username' => $this->username,
			'email' => $this->email,
			'status' => $this->status,
			'option' => [
				'page' => $this->page,
				'item' => $this->item,
			],
		]);

		foreach ($users->items as $user) {
			$desc = (new ProfilePhoto($user->username))->build()
				. $user->name
				. ($isAdmin ? '<span class="email">('.$user->username.($user->email ? ' : '.$user->email : '').')</span>' : '');

			$result[] = [
				'value' => $user->userId,
				'label' => htmlspecialchars($user->name),
				'altLabel' => $desc,
			];
		}
		if ($users->count >= $this->item) $result[] = ['value' => '...', 'label' => 'ยังมีอีก', 'desc' => ''];

		if (debug('api')) {
			$result[] = ['value' => 'num_rows','label' => 'Result is '.count($users).' row(s).'];
		}

		return $result;
	}
}
?>