<?php
function qt_group($self,$qtgrid=NULL) {
	$ret='<h2>ชุดแบบสอบถาม</h2>';

	// List of all quotation in system
	$stmt='SELECT * FROM %qtgroup%';
	$dbs=mydb::select($stmt);

	$ui=new Ui();
	foreach ($dbs->items as $rs) {
		$ui->add('<a href="'.url('qt/group/'.($rs->template?$rs->template:$rs->qtgrid)).'">'.$rs->name.'</a>');
	}
	$ret.=$ui->build();
	return $ret;
}
?>