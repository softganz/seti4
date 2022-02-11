<?php
function r_garage_job_status_update($shopid,$tpid,$status) {
	$stmt='UPDATE %garage_job% SET `jobstatus`=:status WHERE `shopid`=:shopid AND `tpid`=:tpid LIMIT 1';
	mydb::query($stmt, ':shopid',$shopid, ':tpid',$tpid, ':status',$status);
}
?>