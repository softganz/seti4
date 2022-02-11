<?php
function org_seedfund_report($self) {
	$self->theme->title='กองทุนเมล็ดพันธุ์';
	R::Page('org.seedfund.toolbar',$self);

	$ui=new Ui();
	$ui->add('<a href="'.url('org/seedfund/report/need').'">รายงานความต้องการเมล็ดพันธุ์</a>');
	$ret.=$ui->build();
	return $ret;
}
?>