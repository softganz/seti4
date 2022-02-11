<?php
/**
 * Get saveup member information by member id
 *
 * @param String $mid
 * @return Object
 */
function r_saveup_member_get($mid) {
	$result = NULL;

	if (empty($mid)) return NULL;

	$stmt = 'SELECT
						m.*
					, u.`username`
					, u.`name` `userRefName`
					, u.`email`
					FROM %saveup_member% m
						LEFT JOIN %users% u ON u.`uid` = m.`userid`
					WHERE `mid` = :mid
					LIMIT 1';
	$rs = mydb::select($stmt, ':mid', $mid);

	if ($rs->_empty) return NULL;

	$rs = mydb::clearprop($rs);
	$rs->name = trim($rs->firstname.' '.$rs->lastname);

	$result->mid = $rs->mid;
	$result->name = $rs->name;
	$result->active = $rs->status == 'active';

	$result->info = $rs;


	$stmt = 'SELECT `card`, SUM(`amt`) `balance` FROM %saveup_memcard% WHERE `mid` = :mid GROUP BY `card`; -- {key: "card"}';
	$dbs = mydb::select($stmt, ':mid', $mid);

	$result->balance = $dbs->items;

	//debugMsg($dbs, '$dbs');

	$result->loan = Array();
	return $result;
}
?>