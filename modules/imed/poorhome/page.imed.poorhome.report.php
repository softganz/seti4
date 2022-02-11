<?php
/**
* Poor System
*
* @param Object $self
* @return String
*/

function imed_poorhome_report($self) {
	$self->theme->title='รายงาน';
	$self->theme->toolbar=R::Page('imed.poorhome.toolbar',$self);

	$ui=new ui();
	$ui->add('<a href="'.url('imed/poorhome/report/summary').'">รายงานภาพรวมสถานการณ์</a>');
	$ui->add('<a href="'.url('imed/poorhome/report/housebroke').'">รายงานบ้านตนเองที่ชำรุด</a>');
	$ret.=$ui->build('ul');
	return $ret;
}
?>