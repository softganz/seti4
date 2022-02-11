<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_get_lock_report_date($tpid=NULL) {
	$rs = mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND ((`formid`="info" AND `part`="period") || (`formid`="ง.1" AND `part`="title")) AND `flag`>='._PROJECT_LOCKREPORT.' ORDER BY `period` DESC LIMIT 1',':tpid',$tpid);
	$ret = NULL;
	if ($rs->detail2) $ret = $rs->detail2;
	else if ($rs->date2) $ret = $rs->date2;
	$locks[$tpid] = $ret;
	return $ret;
}
?>