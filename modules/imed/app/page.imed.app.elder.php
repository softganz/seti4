<?php
function imed_app_elder($self,$psnid) {
	$psn=R::Model('imed.patient.get',$psnid);
	$isAccess=$psn->RIGHT & _IS_ACCESS;

	if (!$isAccess) return message('error',$psn->error);

	R::View('imed.toolbar',$self,'ข้อมูลผู้สูงอายุ','app',$psn);

	$ret.='<p class="notify">กำลังพัฒนา</p>';
	return $ret;
}
?>