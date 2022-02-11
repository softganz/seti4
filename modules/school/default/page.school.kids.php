<?php
function school_kids($self,$orgid = NULL) {
	if ($orgid && is_numeric($orgid)) {
		$schoolInfo=R::Model('school.get',$orgid);
	} else {
		return R::Page('school.kids.overview',$self);
	}

	R::View('school.toolbar',$self,'Person : '.$schoolInfo->name,NULL,$schoolInfo);

	// กรณีไม่ระบุโรงเรียน

	// กรณีบุคคลทั่วไป

	// กรณีบุคคลอื่น

	// กรณีผู้มีสิทธิ์แก้ไข


	$sidebar.=R::View('school.kids.menu',$orgid);
	$self->theme->sidebar.=$sidebar;

	$ret.=R::Page('school.kids.person',NULL,$orgid);
	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>