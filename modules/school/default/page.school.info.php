<?php
function school_info($self,$orgid) {
	if ($orgid) {
		$schoolInfo=R::Model('school.get',$orgid);
	}

	R::View('school.toolbar',$self,$schoolInfo->name,NULL,$schoolInfo);

	$isEditable=$schoolInfo->RIGHT & _IS_EDITABLE;

	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);

	$ui=new Ui(NULL,'ui-card school-main-menu -info');
	$ui->add('<a class="btn -primary" href="'.url('school/kids/person/'.$orgid).'"><img src="//softganz.com/img/img/school-kids.jpg" /><span>{tr:ข้อมูลนักเรียน}</span></a>');
	$ui->add('<a class="btn -primary" href="'.url('school/summary/'.$orgid).'"><img src="//softganz.com/img/img/school-summary.jpg" /><span>{tr:ข้อมูลรายชั้น}</span></a>');
	$ui->add('<a class="btn -primary" href="'.url('school/report/'.$orgid).'"><img src="//softganz.com/img/img/school-analysis.jpg" /><span>{tr:รายงาน}</span></a>');
	if ($isEditable) {
		$ui->add('<a class="btn -primary" href="'.url('school/dashboard/'.$orgid).'"><img src="//softganz.com/img/img/school-dashboard.jpg" /><span>{tr:กำหนดค่า}</span></a>');
	}
	$ret.=$ui->build();


	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>