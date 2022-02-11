<?php
function garage_job_close($self, $jobInfo) {
	$shopInfo=R::Model('garage.get.shop');

	if (empty($jobInfo)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	new Toolbar($self,'ปิดใบสั่งซ่อม - '.$jobInfo->plate,'job',$jobInfo);

	$newStatus=$jobInfo->isjobclosed=='Yes'?'No':'Yes';
	$stmt='UPDATE %garage_job%
				SET `isjobclosed`=:newstatus, `jobstatus`=10
				WHERE `tpid`=:tpid LIMIT 1';
	mydb::query($stmt,':tpid',$jobInfo->tpid,':newstatus',$newStatus);
	//$ret.=mydb()->_query;
	location('garage/job/'.$jobInfo->tpid);

	return $ret;
}
?>