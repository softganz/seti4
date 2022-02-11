<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function on_project_calendar_delete($rs, $para) {
	$stmt='DELETE FROM %project_activity% WHERE `calid`=:calid LIMIT 1';
	mydb::query($stmt, ':calid', $rs->id);
	if (mydb::table_exists('%project_actguide%')) {
		$stmt='DELETE FROM %project_actguide% WHERE `calid`=:calid';
		mydb::query($stmt, ':calid', $rs->id);
	}
	return $ret;
}
?>