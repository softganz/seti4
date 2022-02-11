<?php
function school_kids_person($self,$orgid) {
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


	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);


	$isEditable=$schoolInfo->RIGHT & _IS_EDITABLE;

	if ($isEditable) {
		$ret.='<div class="btn-floating -right-bottom">';
		$ret.='<a class="btn -floating -circle48" href="'.url('school/kids/person/add/'.$orgid).'"><i class="icon -addbig -white"></i></a>';
		$ret.='</div>';
	}

	$ret.='<form><select class="form-select"><option>==เลือกชั้นเรียน</option></select> <select class="form-select"><option>==เลือกห้องเรียน</option></select></form>';
	$tables = new Table();
	$tables->caption='รายชื่อเด็กนักเรียน';
	$tables->thead=array('no'=>'','ชื่อ นามสกุล','amt -year'=>'ชั้นปี','amt -weight'=>'น้ำหนัก','amt -height'=>'ส่วนสูง','icons -nowrap'=>'');
	$tables->rows[]=array(1,'สมชาย สุขจริง','ประถม 2',45,140,$isEditable?'<a href="'.url('school/kids/view/'.$orgid).'"><i class="icon -viewdoc"></i></a> <a class="-disable" href=""><i class="icon -edit"></i></a> <i class="icon -delete"></i>':'');
	$tables->rows[]=array(2,'ทดสอบ 2','ประถม 2',46,142,$isEditable?'<a href="'.url('school/kids/view/'.$orgid).'"><i class="icon -viewdoc"></i></a> <i class="icon -edit"></i> <i class="icon -delete"></i>':'');
	$tables->rows[]=array(3,'ทดสอบ 3','ประถม 1',45.5,143,$isEditable?'<a href="'.url('school/kids/view/'.$orgid).'"><i class="icon -viewdoc"></i></a> <i class="icon -edit"></i> <i class="icon -delete"></i>':'');
	$tables->rows[]=array(4,'ทดสอบ 4','ประถม 1',47,146,$isEditable?'<a href="'.url('school/kids/view/'.$orgid).'"><i class="icon -viewdoc"></i></a> <i class="icon -edit"></i> <i class="icon -delete"></i>':'');
	$ret.=$tables->build();

	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>