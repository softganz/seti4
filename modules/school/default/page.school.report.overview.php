<?php
function school_report_overview($self) {
	R::View('school.toolbar',$self,'Situation Analysis : Overview');

	$self->theme->sidebar=R::View('school.report.menu');

	$ret.='<h2>ภาพรวม :: วิเคราะห์สถานการณ์</h2>';

	$ret.='<div class="widget url" data-url="project/situation/weight" ></div>';
	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>