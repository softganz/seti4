<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_default($rs = NULL) {
	$tpid=$rs->tpid;
	$submenu=q(2);
	$ret='';
	$info = $rs->info ? $rs->info : $rs->project;
	$isAdmin=$info->RIGHT & IS_ADMIN;
	$isRight=$info->RIGHT & _IS_ACCESS;
	$isEdit=$info->RIGHT & IS_EDITABLE;

	$ui = new Ui(NULL,'ui-nav -info -sg-text-center');

	if ($rs->tpid) {
		$ui->add('<a href="'.url('project/'.$info->projectset.'/page').'"><i class="icon -home"></i><span class="">หน้าหลัก</span></a>');

		$ui->add('<a href="'.url('project/'.$rs->tpid).'" title="รายละเอียดโครงการ"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');

		if (projectcfg::enable('ปฏิทิน')) $ui->add('<a href="'.url('project/'.$rs->tpid.'/info.calendar').'" title="ปฏิทินกิจกรรมของโครงการ"><i class="icon -calendar"></i><span>ปฏิทิน</span></a>');

		$ui->add('<a href="'.url('project/'.$rs->tpid.'/info.action').'" title="รายงานผู้รับผิดชอบโครงการ"><i class="icon -view"></i><span>บันทึกกิจกรรม</span></a>'
			.($notifyActivity ? '<div id="project-notify">'._NL.'<a href="#">'.$notifyActivity.'</a><div><h3>คำเตือน</h3>'.$notifyActivityMsg.'</div>'._NL.'</div>'._NL
			: '')
		);

		$ui->add('<a href="'.url('project/'.$tpid.'/info.summary').'" title="สรุปโครงการ"><i class="icon -description"></i><span>สรุปโครงการ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');

		if ($isRight) $ui->add('<a href="'.url('project/'.$tpid.'/info.dashboard').'" rel="nofollow"><i class="icon -dashboard"></i><span>แผงควบคุม</span></a>');
	} else {
		$ui->add('<a href="'.url('project').'"><i class="icon -home"></i><span class="">หน้าหลัก</span></a>');
		$ui->add('<a href="'.url('project/list').'"><i class="icon -list"></i><span class="">โครงการ</span></a>');
	}

	$ret .= $ui->build();

	return $ret;
}
?>