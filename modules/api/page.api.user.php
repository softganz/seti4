<?php
/**
* API :: Get user
* Created 2021-01-01
* Modify  2021-02-02
*
* @param 
* @return Array
*
* @usage api/user?q=&&username=&email=
*/

$debug = true;

function api_user($self, $q = NULL, $n = NULL, $p = NULL) {
	$q = SG\getFirst($q,trim(post('q')));
	$n = intval(SG\getFirst($item, post('n'), 10));
	$p = intval(SG\getFirst($p, post('p'), 1));

	$getUsername = post('username');
	$getEmail = post('email');

	$result = array();

	if ((empty($q) && empty($getUsername) && empty($getEmail)) || !i()->ok || _HOST != _REFERER) return '[]';

	$isAdmin = i()->admin;

	if ($q) {
		mydb::where('u.`username` LIKE :q OR u.`name` LIKE :q  OR u.`email` LIKE :q', ':q','%'.$q.'%');
	}

	if ($getUsername) {
		mydb::where('u.`username` = :username', ':username', $getUsername);
	}

	if ($getEmail) {
		mydb::where('u.`email` = :email', ':email', $getEmail);
	}

	mydb::value('$LIMIT$',$n);

	$stmt = 'SELECT `uid`, `username`, `name`, `email`
		FROM %users% u
		%WHERE%
		ORDER BY u.`name` ASC
		LIMIT $LIMIT$';

	$dbs = mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$desc = '<img src="'.model::user_photo($rs->username).'" width="24" height="24" />'
			.$rs->name
			.($isAdmin ? '<span class="email">('.$rs->username.($rs->email ? ' : '.$rs->email : '').')</span>' : '');

		$result[] = array(
			'value' => $rs->uid,
			'label' => htmlspecialchars($rs->name),
			'altLabel' => $desc,
		);
	}
	if ($dbs->_num_rows>=$n) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');

	if (debug('api')) {
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	return $result;
}
?>