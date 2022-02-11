<?php
function school_kids_person_add($self,$orgid) {
	if ($orgid && is_numeric($orgid)) {
		$schoolInfo=R::Model('school.get',$orgid);
	} else {
		return R::Page('school.kids.overview',$self);
	}

	R::View('school.toolbar',$self,'Eat : '.$schoolInfo->name,NULL,$schoolInfo);

	// กรณีไม่ระบุโรงเรียน

	// กรณีบุคคลทั่วไป

	// กรณีบุคคลอื่น

	// กรณีผู้มีสิทธิ์แก้ไข

	$isEditable=$schoolInfo->RIGHT & _IS_EDITABLE;

	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);

	$data=(object)post('data');
	if ($data->save) {
		// Save
		location('school/kids/person/'.$orgid);
	}

	$ret.=R::View('school.kids.person.form',$orgid,$data);

	//$ret.=print_o($data,'$data');
	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>