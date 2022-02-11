<?php
function school_kids_overview($self) {
	R::View('school.toolbar',$self,'Kids Personal : Overview');

	$self->theme->sidebar=R::View('school.report.menu');


	$ret.='<h2>ภาพรวม :: ข้อมูลเด็กนักเรียน</h2>';
	$ret.='<p>แสดงจำนวนเด็กทั้งหมดในปัจจุบัน รายชั้น</p>';

	return $ret;
}
?>