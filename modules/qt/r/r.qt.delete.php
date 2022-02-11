<?php
/**
* QT : Delete QT
* Created 2020-01-01
* Modify  2020-10-25
*
* @param Int $qtRef
*/

$debug = true;

function r_qt_delete($qtRef) {
	if (empty($qtRef)) return;

	$stmt = 'DELETE FROM %qttran% WHERE `qtref` = :qtRef';
	mydb::query($stmt, ':qtRef', $qtRef);

	//debugMsg(mydb()->_query);

	$stmt = 'DELETE FROM %qtmast% WHERE `qtref` = :qtRef LIMIT 1';
	mydb::query($stmt, ':qtRef', $qtRef);

	//debugMsg(mydb()->_query);
}
?>