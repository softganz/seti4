<?php
/**
* Project detail
*
* @param Object $projectInfo
* @param JSON String $options
* @return String
*/
function view_project_nav_default($projectInfo = NULL, $options = NULL) {
	$tpid=$projectInfo->tpid;
	$submenu=q(2);

	$projectset = SG\getFirst($projectInfo->info->projectset, $projectInfo->project->projectset);
	$right = SG\getFirst($projectInfo->info->RIGHT, $projectInfo->project->RIGHT);

	$isAdmin=$right & _IS_ADMIN;
	$isRight=$right & _IS_EDITABLE;
	$isEdit=$right & _IS_EDITABLE;

	$ret='';

	if ($tpid) {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('project/set/'.$projectset).'"><i class="icon -home"></i><span class="-hidden">โครงการ</span></a>');
		$ui->add('<a href="'.url('paper/'.$tpid).'" title="รายละเอียดโครงการ"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');
		if (projectcfg::enable('ปฏิทิน')) $ui->add('<a href="'.url('project/'.$tpid.'/info.calendar').'" title="ปฏิทินกิจกรรมของโครงการ"><i class="icon -calendar"></i><span>ปฏิทินโครงการ</span></a>');
		$ui->add('<a href="'.url('paper/'.$tpid.'/owner').'" title="บันทึกกิจกรรม"><i class="icon -view"></i><span>บันทึกกิจกรรม</span></a>');
		$ui->add('<a href="'.url('project/data/'.$tpid).'"><i class="icon -viewdoc"></i><span>แบบบันทึกข้อมูล</span></a>');

		//if (projectcfg::enable('trainer')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/trainer').'" title="รายงาของพี่เลี้ยงโครงการ" ><i class="icon -view"></i><span>รายงานพี่เลี้ยง</span></a>');

		if ($isRight) $ui->add('<a href="'.url('paper/'.$tpid.'/info/menu').'"><i class="icon -dashboard"></i><span>แผงควบคุม</span></a>');

		if ($options->showPrint) $ui->add('<a href="javascript:window.print()"><i class="icon -print"></i></a>');
		$ret.=$ui->build()._NL;

		/*
		if ($submenu=='owner') {
			$ui=new Ui(NULL,'ui-nav -ownerreport');
			if (projectcfg::enable('ง.1')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/m1').'">รายงาน ง.1</a>');
			if (projectcfg::enable('ส.1')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/s1').'">รายงาน ส.1</a>');
			if (projectcfg::enable('ส.2')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/s2').'">รายงาน ส.2</a>');
			if (projectcfg::enable('ง.2')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/m2').'">รายงาน ง.2</a>');
			if (projectcfg::enable('ส.3')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/s3').'">รายงาน ส.3</a>');
			if (projectcfg::enable('ส.4')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/s4').'">รายงาน ส.4</a>');
			
			$ui->add('<a href="'.url('paper/'.$rs->tpid.'/info/evaluation').'" title="การติดตามประเมินผลการดำเนินกิจกรรมของโครงการ" xrel="#content-body">สรุปกิจกรรม</a>');

			if (cfg('project.sendfile') && substr($rs->project->agrno,0,2)=='54' && (user_access('administer projects') || project_model::is_owner_of($rs->tpid) || project_model::is_trainer_of($rs->tpid))) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/activity').'">'.tr('ส่งไฟล์รายงานกิจกรรม').'</a></li><li><a href="'.url('paper/'.$rs->tpid.'/owner/progress').'">'.tr('ส่งไฟล์รายงานความก้าวหน้า').'</a></li><li><a href="'.url('paper/'.$rs->tpid.'/owner/financial').'">'.tr('ส่งไฟล์รายงานการเงิน').'</a>');
			$ret.=$ui->build()._NL;
		} else if ($submenu=='trainer') {
			$ui=new Ui(NULL,'ui-nav -ownerreport');
			$ui->add('<a href="'.url('paper/'.$rs->tpid.'/trainer/follow').'" title="แบบบันทึกการติดตามสนับสนุนโครงการ">'.tr('บันทึกการติดตาม').'</a>');
			$ui->add('<a href="'.url('paper/'.$rs->tpid.'/trainer/valuation').'" title="แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง">'.tr('แบบประเมิน').'</a>');
			if (cfg('project.sendfile') && substr($rs->project->agrno,0,2)=='54' && (user_access('administer projects') || project_model::is_trainer_of($rs->tpid))) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/trainer/activity').'">'.tr('ส่งไฟล์รายงานกิจกรรม').'</a></li><li><a href="'.url('paper/'.$rs->tpid.'/trainer/follow').'">'.tr('ส่งไฟล์รายงานการติดตาม').'</a>');
			$ret.=$ui->build()._NL;
		}
		*/
	} else {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('project/set/3256').'"><i class="icon -home"></i><span class="-hidden">โครงการ</span></a>');
		$ui->add('<a href="'.url('project/list',array('set'=>3256)).'"><i class="icon -list"></i><span class="-hidden">รายชื่อโครงการ</span></a>');
		$ret.=$ui->build();
	}

	//debugMsg($projectInfo,'$projectInfo');
	return $ret;
}
?>