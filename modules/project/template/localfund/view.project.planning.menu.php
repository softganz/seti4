<?php
function view_project_planning_menu() {
	$ui=new Ui();
	$ui->add('<a href="'.url('project/planning',array('by'=>'area')).'">เขต</a>');
	$ui->add('<a href="'.url('project/planning',array('by'=>'prov')).'">จังหวัด</a>');
	$ui->add('<a href="'.url('project/planning',array('by'=>'issue')).'">แผนงาน</a>');
	$ret='<nav class="nav -ver">'.$ui->build().'</nav>';
	return $ret;
}
?>