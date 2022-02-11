<?php
function r_garage_job_tr_delete($tpid,$id,$options='{}') {
	$defaults='{debug:false}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	if (empty($id)) return;
	$stmt='DELETE FROM %garage_jobtr% WHERE `tpid`=:tpid AND `jobtrid`=:id LIMIT 1';
	mydb::query($stmt, ':tpid',$tpid, ':id',$id);
	if ($debug) debugMsg(mydb()->_query);
	return $data->trid;
}
?>