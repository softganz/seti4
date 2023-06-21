<?php
/**
* API     :: User API
* Created :: 2022-11-19
* Modify  :: 2022-11-19
* Version :: 2
*
* @return Array
*
* @usage api/user
*/

class UserApi extends PageApi {
	var $queryText;
	var $username;
	var $email;
	var $page;
	var $items;

	function __construct() {
		parent::__construct([
			'queryText' => \SG\getFirst(post('q')),
			'username' => post('username'),
			'email' => post('email'),
			'page' => \SG\getFirst(post('page'), post('p'), 1),
			'items' => \SG\getFirst(post('item'), post('n'), 10),
		]);
	}

	function build() {
		$result = [];

		// Only referer form same site
		if ((empty($this->queryText) && empty($this->username) && empty($this->email)) || !i()->ok || _HOST != _REFERER) return $result;

		$isAdmin = i()->admin;

		if ($this->queryText) {
			mydb::where('u.`username` LIKE :q OR u.`name` LIKE :q  OR u.`email` LIKE :q', ':q','%'.$this->queryText.'%');
		}

		if ($this->username) {
			mydb::where('u.`username` = :username', ':username', $this->username);
		}

		if ($this->email) {
			mydb::where('u.`email` = :email', ':email', $this->email);
		}

		mydb::value('$LIMIT$',$this->items);

		$dbs = mydb::select(
			'SELECT `uid`, `username`, `name`, `email`
			FROM %users% u
			%WHERE%
			ORDER BY u.`name` ASC
			LIMIT $LIMIT$'
		);

		// debugMsg($dbs, '$dbs');
		foreach ($dbs->items as $rs) {
			$desc = '<img src="'.BasicModel::user_photo($rs->username).'" width="24" height="24" />'
				.$rs->name
				.($isAdmin ? '<span class="email">('.$rs->username.($rs->email ? ' : '.$rs->email : '').')</span>' : '');

			$result[] = array(
				'value' => $rs->uid,
				'label' => htmlspecialchars($rs->name),
				'altLabel' => $desc,
			);
		}
		if ($dbs->_num_rows>=$this->items) $result[] = ['value'=>'...','label'=>'ยังมีอีก','desc'=>''];

		if (debug('api')) {
			$result[] = ['value'=>'query','label'=>$dbs->_query];
			$result[] = ['value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).'];
		}
		return $result;
	}
}
?>