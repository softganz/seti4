<?php
function qt($self,$qt=NULL) {
	$ret='<h2>แบบสอบถาม</h2>';

	// List of all quotation in system
	$ui=new Ui();
	$ui->add('<a href="'.url('qt/group/102').'">แบบประเมินผลการอบรมหลักสูตรการพัฒนาศักยภาพภาคีเครือข่าย สสส.</a>');
	$ret.=$ui->build();
	return $ret;
}
?>