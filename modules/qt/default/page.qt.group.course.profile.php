<?php
function qt_group_course_profile($self,$uid=NULL) {
	R::View('toolbar',$self,'ข้อมูลทั่วไป','qt.course');

	// Check is assessor register
	$stmt='SELECT * FROM %person_group% WHERE `groupname`="assessor" AND `uid`=:uid LIMIT 1';
	$assessorInfo=mydb::select($stmt,':uid',$uid);

	if ($assessorInfo->_empty) return message('error','ข้อมูลไม่ถูกต้อง');

	$psnInfo=R::Model('person.get',$assessorInfo->psnid);

	$stmt='SELECT * FROM %person_tr% WHERE `psnid`=:psnid LIMIT 1';
	$info=mydb::select($stmt,':psnid',$psnInfo->psnid);

	$psnInfo->info->age=round($info->num1);
	$psnInfo->info->yearexp=round($info->num2);

	$ret.=R::View('qt.group.course.profile',$psnInfo);
	//$ret.=R::Page('project.assessor',NULL,$assessorInfo->psnid);

	//$ret.=print_o($info);
	//$ret.=print_o($psnInfo);

	return $ret;
}
?>