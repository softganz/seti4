<?php
function r_project_getarea($tpid) {
	$ret='';
	$debug=false;

	if (empty($tpid)) return;

	$stmt='SELECT
					`tambon`,`ampur`,`changwat`
				FROM %project% p
				WHERE p.`tpid`=:tpid
				LIMIT 1';
	$rs=mydb::select($stmt,':tpid',$tpid);

	$ret.=print_o($rs,'$rs');
	return $ret;
}
?>