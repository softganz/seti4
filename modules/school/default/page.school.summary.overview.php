<?php
function school_summary_overview($self) {
	R::View('school.toolbar',$self,'Kids Summary : Overview');

	$self->theme->sidebar=R::View('school.report.menu');


	$ret.='<h2>ภาพรวม :: สถานการณ์</h2>';
	$ret.='<p>แผนที่แสดงภาวะอ้วน-ผอม รายจังหวัด</p>';

	return $ret;
}
?>