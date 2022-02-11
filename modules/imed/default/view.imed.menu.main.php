<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function view_imed_menu_main() {
	$isAdmin = user_access('administer imeds');

	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('imed/my').'" data-rel="#imed-app"><i class="icon -material">account_circle</i><span>สถานะ</span></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/my/care').'" data-rel="#imed-app"><i class="icon -material">accessible</i><span>ดูแล</span></a>');
	$ui->add('<a class="x-sg-action" href="'.url('imed/need').'" data-rel="#imed-app"><i class="icon -material">how_to_reg</i><span>ความต้องการ</span></a>');
	$ui->add('<a href="'.url('imed/social').'"><i class="icon -material">group</i><span>กลุ่ม</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('imed/my/status').'" data-rel="#imed-my-status"><i class="icon -material">group</i><span>เพื่อน</span></a>');
	$ui->add('<a href="'.url('imed/pocenter').'"><i class="icon -material">accessible_forward</i><span>ศูนย์กายอุปกรณ์</span></a>');
	$ui->add('<a class="sg-action" href="'.url('imed/report').'" data-rel="#imed-app"><i class="icon -material">assessment</i><span>รายงาน</span></a>');
	if ($isAdmin) {
		$ui->add('<a class="sg-action" href="'.url('imed/admin').'" data-rel="#imed-app"><i class="icon -material">settings</i><span>จัดการระบบ</span></a>');
	}
	return $ui;
}
?>