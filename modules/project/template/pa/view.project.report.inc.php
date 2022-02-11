<?php

/**
 * Send Document Report
 *
 */
function view_project_report_inc() {
	$menu = '<header class="header"><h3>สถานการณ์</h3></header>';

	$ui = new Ui(NULL, 'ui-menu project-report-menu');
	$ui->add('<a href="'.url('project/report/pasit').'">สถานการณ์สุขภาพ</a>');
	//$ui->add('<a href="'.url('project/report/patargetsit').'">สถานการณ์กลุ่มเป้าหมาย</a>');

	$menu .= $ui->build();

	return $menu;
}
?>