<?php
function ad_report($self) {
	$self->theme->title='Advertisment Report';
	$ui=new Ui();
	$ui->add('<a href="'.url('ad/report/adclick').'">สถิติการคลิก</a>');

	$ret.=$ui->build();
	return $ret;
}
?>