<?php
function r_project_trainer_org($orgid,$uid=NULL) {
	$result=false;
	//debugMsg('orgid='.$orgid);
	$stmt='SELECT * FROM %org_officer% WHERE `orgid`=:orgid AND `uid`=:uid AND `membership`="trainer" LIMIT 1';
	$rs=mydb::select($stmt,':orgid',$orgid,':uid',$uid);
	//debugMsg($rs,'$rs');
	$result=$rs->uid;
	return $result;
}
?>