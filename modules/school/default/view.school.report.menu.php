<?php
function view_school_report_menu() {
	$ret='';
	$ui=new Ui(NULL,'ui-card school-main-menu');
	$ui->add('<a class="btn -primary" href="'.url('school/report').'"><img src="//softganz.com/img/img/school-analysis.jpg" /><span>{tr:ภาพรวม}</span></a>');
	$ui->add('<a class="btn -primary" href="'.url('school/kids/').'"><img src="//softganz.com/img/img/school-kids.jpg" /><span>{tr:ภาวะอ้วน-เตี้ย}</span></a>');
	$ui->add('<a class="btn -primary" href="'.url('school/summary/').'"><img src="//softganz.com/img/img/school-summary.jpg" /><span>{tr:ภาวะการกินอาหาร}</span></a>');
	$ret.=$ui->build();
	return $ret;
}
?>