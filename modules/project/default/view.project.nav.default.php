<?php
/**
* Project detail
*
* @param Object $self
* @param Object $projectInfo
* @param Object $options
* @return String
*/
function view_project_nav_default($projectInfo, $options = NULL) {
	$tpid = $projectInfo->tpid;
	$submenu = q(2);
	$ret = '';
	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isRight = $projectInfo->RIGHT & _IS_ACCESS;
	$isEdit = $projectInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_TRAINER);

	$orgShortName = SG\getFirst($projectInfo->info->orgShortName, $projectInfo->project->orgShortName);

	$ui = new Ui(NULL,'ui-nav -info -sg-text-center');
	$dropboxUi=new Ui(NULL,'ui-dropbox');

	if ($projectInfo->tpid) {
		if ($projectInfo->info->orgid) {
			$ui->add('<a href="'.url('org/'.$projectInfo->info->orgid).'"><i class="icon -material">home</i><span class="">องค์กร</span></a>');
		} else if ($projectInfo->info->projectset) {
			$ui->add('<a href="'.url('project/'.$projectInfo->info->projectset).'"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>');
		} else {
			$ui->add('<a href="'.url('project').'"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>');
		}

		$ui->add('<a href="'.url('project/my/project').'"><i class="icon -material">person</i><span class="">ของฉัน</span></a>');

		$ui->add('<sep>');

		$ui->add('<a href="'.url('project/'.$tpid).'" title="รายละเอียดโครงการ"><i class="icon -material">find_in_page</i><span>รายละเอียด</span></a>');
		if (projectcfg::enable('ปฏิทิน')) $ui->add('<a href="'.url('project/'.$tpid.'/info.calendar').'" title="ปฏิทินกิจกรรมของโครงการ"><i class="icon -material">event</i><span>ปฏิทิน</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/situation').'" title="บันทึกสถานการณ์"><i class="icon -material">assessment</i><span>สถานการณ์</span></a>',array('class'=>'-situation'));
		$ui->add('<a href="'.url('project/'.$tpid.'/info.action').'" title="บันทึกกิจกรรม"><i class="icon -material">assignment</i><span>บันทึกกิจกรรม</span></a>');
		$ui->add('<sep>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.evalform').'" title="แบบประเมิน"><i class="icon -material">assessment</i><span class="">แบบประเมิน</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.summary').'" title="สรุปโครงการ"><i class="icon -material">description</i><span>สรุปโครงการ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');
		if ($isRight) $ui->add('<a href="'.url('project/'.$tpid.'/info.dashboard').'" rel="nofollow"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>');

		if ($options->showPrint) {
			$ui->add('<sep>');
			$ui->add('<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>');
		}

		if ($projectInfo->info->proposalId) {
			$dropboxUi->add('<a href="'.url('project/develop/'.$tpid).'" title="พัฒนาโครงการ"><i class="icon -material">pageview</i><span>พัฒนาโครงการ</span></a>');
		}
	} else {
		$ui->add('<a href="'.url('project').'"><i class="icon -material">home</i><span class="">โครงการ</span></a>');
		$ui->add('<a href="'.url('project/list').'"><i class="icon -material">view_list</i><span class="">รายชื่อโครงการ</span></a>');
		$ui->add('<a href="'.url('project/map').'"><i class="icon -material">place</i><span class="">แผนที่</span></a>');
	}
	$ret.=$ui->build()._NL;

	if (i()->ok) {
		$dropboxUi->add('<a href="'.url('project/my/action/*').'" title="กิจกรรมล่าสุด"><i class="icon -material">person</i><span>กิจกรรมล่าสุด</span></a>');
		$dropboxUi->add('<a href="'.url('project/my/setting').'" title="ตั้งค่า"><i class="icon -material">settings</i><span>ตั้งค่า</span></a>');
	}

	if ($isAdmin) {
		$dropboxUi->add('<sep>');
		$dropboxUi->add('<a href="'.url('project/admin').'" title="Admin"><i class="icon -material">settings</i><span>Project Administrator</span></a>');
	}

	if ($dropboxUi->count()) $ret .= sg_dropbox($dropboxUi->build('ul'),'{class:"leftside -atright"}');

	return $ret;
}
?>