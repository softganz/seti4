<?php
/**
* Admin Repair Like Times
*
* @param Object $self
* @return String
*/

$debug = true;

function admin_repair_like($self) {
	$ret = '';
	$stmt = 'UPDATE
					%topic% AS t
					SET
						t.`liketimes` = (
							SELECT COUNT(*)
							FROM %reaction% m
							WHERE m.`refid` = t.`tpid`
							AND m.`action` IN ("PROJ.LIKE","PDEV.LIKE","TOPIC.LIKE")
						)
					';
	mydb::query($stmt);

	$ret .= mydb()->_query;

	return $ret;
}
?>