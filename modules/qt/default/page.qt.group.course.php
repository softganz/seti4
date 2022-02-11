<?php
function qt_group_course($self,$qtgrid=NULL) {
	R::View('toolbar',$self,'แบบประเมินผลการอบรมหลักสูตรการพัฒนาศักยภาพภาคีเครือข่าย สสส.','qt.course');

	$ret.='<div class="-sg-text-center" style="padding:0 16px;"><a class="btn -primary" href="'.url('qt/group/course/take').'" style="padding:16px;"><i class="icon -addbig -white"></i><span>บันทึกแบบประเมินผลการอบรมหลักสูตรการพัฒนาศักยภาพภาคีเครือข่าย สสส.</span></a>';

	//$ret.='<br /><br /><a class="btn" href="'.url('qt/group/course/experience').'"><i class="icon -addbig"></i><span>บันทึกผลการนำความรู้ไปใช้ประโยชน์ในงานเครือข่าย</span></a>';
	$ret.='</div>';

	// List of all quotation in system
	/*
	$stmt='SELECT * FROM %qtgroup%';
	$dbs=mydb::select($stmt);

	$ui=new Ui();
	foreach ($dbs->items as $rs) {
		$ui->add('<a href="'.url('qt/group/'.($rs->template?$rs->template:$rs->qtgrid)).'">'.$rs->name.'</a>');
	}
	//$ret.=$ui->build();
	*/
	$ret.=R::Page('qt.group.course.experience',NULL);
	return $ret;
}
?>