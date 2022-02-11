<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_default($rs = NULL, $options = '{}') {
	$tpid=$rs->tpid;
	$submenu=q(2);
	$ret='';
	$isEdit=user_access('administer projects','edit own project content',$rs->uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);

	if ($rs->tpid) {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('org/'.$rs->orgId).'"><i class="icon -material">info</i><span class="">โรงเรียน</span></a>');
		$ui->add('<a href="'.url('project/'.$rs->tpid).'" title="รายละเอียดโครงการ"><i class="icon -material">find_in_page</i><span>รายละเอียด</span></a>');
		if (projectcfg::enable('ปฏิทิน')) $ui->add('<a href="'.url('project/'.$rs->tpid.'/info.calendar').'" title="ปฏิทินกิจกรรมของโครงการ"><i class="icon -calendar"></i><span>ปฏิทิน</span></a>');
		$ui->add('<a href="'.url('project/'.$rs->tpid.'/info.situation').'" title="สถานการณ์"><i class="icon -material">insights</i><span>สถานการณ์</span></a>');
		$ret.=$ui->build()._NL;

		$ui=new Ui(NULL,'ui-nav -report');
		$ui->add('<a href="'.url('project/'.$rs->tpid.'/info.action').'" title="รายงานผู้รับผิดชอบโครงการ"><i class="icon -material">assignment</i><span>กิจกรรม</span></a>');

		$ui->add('<a href="'.url('project/'.$tpid.'/info.evalform').'" title="แบบประเมิน"><i class="icon -report"></i><span class="">แบบประเมิน</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.summary').'" title="สรุปโครงการ"><i class="icon -material">assignment</i><span class="">สรุปโครงการ</span></a>');

		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');
		if ($isEdit) $ui->add('<a href="'.url('project/'.$tpid.'/info.dashboard').'"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>');
		$ret.=$ui->build()._NL;
	} else {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('project').'"><i class="icon -home"></i><span class="">โครงการ</span></a>');
		$ret.=$ui->build();
	}

	return $ret;
}
?>