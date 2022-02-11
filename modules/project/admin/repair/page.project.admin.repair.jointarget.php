<?php
/**
* Repair Real Join Target Person Amount From Table project_target
* Created 2019-12-07
* Modify  2019-12-07
*
* @param Object $self
* @return String
*/

$debug = true;

function project_admin_repair_jointarget($self) {
	$ret = '';

	$stmt = 'UPDATE `sgz_project` p SET p.`jointarget` =
		(SELECT SUM(IFNULL(`joinamt`,0)) `joinTotal`
		FROM `sgz_project_target` 
		WHERE `tpid` = p.`tpid` AND `tagname` = "info")';

	mydb::query($stmt);

	$ret .= mydb()->_query.'<br />';

	$stmt = 'UPDATE `sgz_project` SET `jointarget` = NULL WHERE `jointarget` = 0';

	mydb::query($stmt);

	$ret .= mydb()->_query.'<br />';
	return $ret;
}
?>