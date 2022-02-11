<?php
/**
* Project detail
*
* @param Object $self
* @param Object $projectInfo
* @param Object $para
* @return String
*/
function view_project_nav_set($projectInfo = NULL, $options = NULL) {
	$tpid = $projectInfo->tpid;
	$isAdmin = $projectInfo->info->RIGHT & IS_ADMIN;
	$isRight = $projectInfo->info->RIGHT & _IS_ACCESS;
	$isEdit = $projectInfo->info->RIGHT & IS_EDITABLE;

	$ret = '';

	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$ui->add('<a href="'.url('project/set').'" title="หน้าหลัก"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('project/my').'" title="ของฉัน"><i class="icon -material">person</i><span class="">ของฉัน</span></a>');

	if ($projectInfo->tpid) {
		$ui->add('<sep>');
		$ui->add('<a href="'.url('project/set/'.$projectInfo->tpid).'" title="รายละเอียด"><i class="icon -material">find_in_page</i><span class="">รายละเอียด</span></a>');
		$ui->add('<a href="'.url('project/'.$projectInfo->tpid.'/info.action').'" title="บันทึกกิจกรรม"><i class="icon -material">assignment</i><span class="">กิจกรรม</span></a>');
		$ui->add('<sep>');
		if ($projectInfo->info->ischild) {
			$ui->add('<a href="'.url('project/'.$projectInfo->tpid.'/sub').'" title="โครงการย่อย"><i class="icon -material">view_list</i><span class="">โครงการย่อย</span></a>');
		}
		$ui->add('<a href="'.url('project/'.$projectInfo->tpid.'/info.evalform').'" title="แบบประเมิน"><i class="icon -material">assessment</i><span class="">แบบประเมิน</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.summary').'" title="รายงานสรุป"><i class="icon -material">description</i><span>รายงานสรุป</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$projectInfo->tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');
		if ($isRight) $ui->add('<a href="'.url('project/'.$tpid.'/info.dashboard').'" rel="nofollow"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>');
	}

	$ret .= $ui->build();

	//$ret .= print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>