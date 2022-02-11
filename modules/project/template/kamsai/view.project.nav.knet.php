<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_knet($orgInfo = NULL, $options = '{}') {
	$orgId = $orgInfo->orgid;

	$ret='';

	$ui = new Ui(NULL,'ui-nav -info -sg-text-center');

	$ui->add('<a href="'.url('project/knet').'"><i class="icon -material">home</i><span class="">ศูนย์เรียนรู้</span></a>');
	//$ui->add('<a href="'.url('project/knet/my').'"><i class="icon -material">account_circle</i><span class="">โรงเรียนในความรับผิดชอบ</span></a>');

	if ($orgId) {

		//$ui->add('<a href="'.url('paper/'.$orgId).'" title="รายละเอียดโครงการ"><i class="icon -viewdoc"></i><span>โรงเรียนแม่ข่าย</span></a>');
		if ($orgInfo->info->networktype == 1) {
			$ui->add('<a href="'.url('project/knet/'.$orgId).'" title="โรงเรียนแม่ข่าย"><i class="icon -material">account_balance</i><span>โรงเรียนแม่ข่าย</span></a>');
			//$ui->add('<a href="'.url('project/knet/'.$orgId.'/school').'" title="โรงเรียนเครือข่าย"><i class="icon -material">view_list</i><span>โรงเรียนเครือข่าย</span></a>');
			$ui->add('<a href="'.url('project/knet/'.$orgId.'/action').'" title="กิจกรรมเครือข่าย"><i class="icon -material">assignment</i><span>กิจกรรมเครือข่าย</span></a>');
		} else {
			$ui->add('<a href="'.url('project/knet/'.$orgInfo->info->parent).'" title="โรงเรียนแม่ข่าย"><i class="icon -material">account_balance</i><span>โรงเรียนแม่ข่าย</span></a>');
			$ui->add('<a href="'.url('project/knet/'.$orgInfo->info->parent.'/action').'" title="กิจกรรมเครือข่าย"><i class="icon -material">assignment</i><span>กิจกรรมเครือข่าย</span></a>');
	}
	} else {
	}

	if ($options->showPrint) {
		$ui->add('<sep>');
		$ui->add('<a href="javascript:window.print()"><i class="icon -print"></i><span>พิมพ์</span></a>');
	}

	$ret.=$ui->build()._NL;

	return $ret;
}
?>