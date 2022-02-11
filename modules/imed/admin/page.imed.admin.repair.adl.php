<?php
/**
* Repair Person ADL From Last Record of Table imed_barthel
* Created 2020-01-15
* Modify  2020-01-15
*
* @param Object $self
* @return String
*/

$debug = true;

function imed_admin_repair_adl($self) {
	$ret = '';

	$stmt = 'UPDATE sgz_db_person p SET `adl` = (SELECT `score` FROM sgz_imed_barthel WHERE `psnid`=p.`psnid` ORDER BY `seq` DESC LIMIT 1)';

	mydb::query($stmt);

	$ret .= mydb()->_query;
	
	return $ret;
}
?>