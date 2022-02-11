<?php
function view_project_nav_admin() {
	$ret = '';
	$ui = new Ui(NULL,'ui-nav -info');
	$ui->add('<a href="'.url('project').'"><i class="icon -home"></i><span class="-hidden">โครงการ</span></a>');
	$ui->add('<a href="'.url('project/admin').'"><i class="icon -setting"></i><span class="-hidden">Setting</span></a>');
	$ret .= $ui->build();
	return $ret;
}
?>