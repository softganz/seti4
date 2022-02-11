<?php
/**
* Project People Qt Get data
*
* @param Int $tpid
* @param Int $qtref
* @return Object
*/

function r_project_qt_people_get($tpid, $qtref) {
	$result = NULL;

	$stmt = 'SELECT * FROM %qtmast% WHERE `qtref` = :qtref AND `tpid` = :tpid LIMIT 1';

	$rs = mydb::select($stmt, ':qtref', $qtref, ':tpid', $tpid);

	$result = $rs;

	$stmt = 'SELECT * FROM %qttran% WHERE `qtref` = :qtref; -- {key:"part"}';

	$result->trans = mydb::select($stmt, ':qtref', $qtref)->items;

	//debugMsg($rs,'$rs');
	return $result;
}
?>