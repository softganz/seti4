<?php
/**
* Project detail
*
* @param Object $self
* @param Object $projectInfo
* @param Object $para
* @return String
*/
function view_publicmon_default_nav($info = NULL, $options = NULL) {
	$tpid = $projectInfo->tpid;
	$isAdmin = 1 || $info->info->RIGHT & IS_ADMIN;
	$isRight = $info->info->RIGHT & _IS_ACCESS;
	$isEdit = $info->info->RIGHT & IS_EDITABLE;

	$ret = '';

	if (1||$projectInfo->tpid) {
		$ui= new Ui(NULL, 'ui-nav -info');
		$ui->add('<a href="'.url('publicmon/home').'" title="หน้าหลัก"><i class="icon -home"></i><span>หน้าหลัก</span></a>');
		$ui->add('<a href="'.url('publicmon/report').'" title="วิเคราะห์"><i class="icon -report"></i><span>วิเคราะห์</span></a>');
		if ($isAdmin)
			$ui->add('<a href="'.url('publicmon/manage').'" title="จัดการระบบ"><i class="icon -setting"></i><span>จัดการ</span></a>');
		$ret .= $ui->build();
	}

	//$ret .= print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>