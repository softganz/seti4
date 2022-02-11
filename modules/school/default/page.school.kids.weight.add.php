<?php
function school_kids_weight_add($self,$orgid) {
	if ($orgid && is_numeric($orgid)) {
		$schoolInfo=R::Model('school.get',$orgid);
	} else {
		return R::Page('school.kids.overview',$self);
	}

	R::View('school.toolbar',$self,'Weight & Height : '.$schoolInfo->name,NULL,$schoolInfo);

	// กรณีไม่ระบุโรงเรียน

	// กรณีบุคคลทั่วไป

	// กรณีบุคคลอื่น

	// กรณีผู้มีสิทธิ์แก้ไข

	$isEditable=$schoolInfo->RIGHT & _IS_EDITABLE;

	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);

	$data=(object)post('data');
	if ($data->save) {
		// Save
		location('school/kids/weight/'.$orgid);
	}

	$ret.='<a class="btn" href="'.url('school/kids/weight/add/'.$orgid).'">บันทึกรายคน</a> <a class="btn" href="'.url('school/kids/weight/add/'.$orgid,array('form'=>'class')).'">บันทึกทั้งห้องเรียน</a>';

	if (post('form')=='class') {
		$ret.=R::View('school.kids.weight.form.class',$orgid,$data);
	} else {
		$ret.=R::View('school.kids.weight.form',$orgid,$data);
	}

	//$ret.=print_o($data,'$data');
	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>