<?php
function r_garage_job_tr_get($tpid,$trid,$options='{}') {
	$defaults='{debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;
	
	$rs=NULL;
	
	$stmt='SELECT
					  tr.*
					, u.`name` `posterName`
					, rc.`repaircode`
					, IF(tr.`description`!="",tr.`description`,rc.`repairname`) `repairname`
					, rc.`priceA`, rc.`priceB`, rc.`priceC`, rc.`priceD`
					FROM %garage_jobtr% tr
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %garage_repaircode% rc USING(`repairid`)
					WHERE `tpid`=:tpid AND `jobtrid`=:trid
					LIMIT 1';
	$rs=mydb::select($stmt, ':tpid',$tpid, ':trid',$trid);

	if ($rs->_num_rows) {
		if (!$debug) mydb::clearprop($rs);
		//debugMsg(mydb()->_query);
	}
	if ($debug) debugMsg($rs,'$rs');
	return $rs;
}
?>