<?php
function school_kids_weight($self,$orgid) {
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


	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);


	$isEditable=$schoolInfo->RIGHT & _IS_EDITABLE;

	if ($isEditable) {
		$ret.='<div class="btn-floating -right-bottom">';
		$ret.='<a class="btn -floating -circle48" href="'.url('school/kids/weight/add/'.$orgid).'"><i class="icon -addbig -white"></i></a>';
		$ret.='</div>';
	}


	$tables = new Table();
	$tables->caption='รายการบันทึกน้ำหนัก/ส่วนสูง';
	$tables->thead=array('no'=>'','ชื่อ นามสกุล','amt -weight'=>'น้ำหนัก','amt -height'=>'ส่วนสูง','date'=>'วันที่เก็บข้อมูล','icons -nowrap'=>'');
	$tables->rows[]=array(1,'ทดสอบ ทดสอบ',45,140,date('d/m/Y'),$isEditable?'<i class="icon -viewdoc"></i> <i class="icon -edit"></i> <i class="icon -delete"></i>':'');
	$tables->rows[]=array(2,'ทดสอบ ทดสอบ',46,142,date('d/m/Y'),$isEditable?'<i class="icon -viewdoc"></i> <i class="icon -edit"></i> <i class="icon -delete"></i>':'');
	$tables->rows[]=array(3,'ทดสอบ ทดสอบ',45.5,143,date('d/m/Y'),$isEditable?'<i class="icon -viewdoc"></i> <i class="icon -edit"></i> <i class="icon -delete"></i>':'');
	$tables->rows[]=array(4,'ทดสอบ ทดสอบ',47,146,date('d/m/Y'),$isEditable?'<i class="icon -viewdoc"></i> <i class="icon -edit"></i> <i class="icon -delete"></i>':'');
	$ret.=$tables->build();

	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>