<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_default($rs, $options = NULL) {
	$tpid = $rs->tpid;
	$submenu = q(2);
	$ret = '';
	$isAdmin = $rs->RIGHT & IS_ADMIN;
	$isRight = $rs->RIGHT & _IS_ACCESS;
	$isEdit = $rs->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_TRAINER);

	$orgShortName = SG\getFirst($rs->info->orgShortName, $rs->project->orgShortName);

	$isExternalLink = $rs->info->link;

	$ui = new Ui(NULL,'ui-nav -info -sg-text-center');

	if ($rs->tpid) {
		$isEvalHia = mydb::select('SELECT `trid` FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "eval-hia" LIMIT 1', ':tpid', $rs->tpid)->_num_rows;

		$ui->add('<a href="'.url('project/my').'"><i class="icon -home"></i><span class="">หน้าหลัก</span></a>');
		//$ui->add('<a href="'.url('project/my/project').'"><i class="icon -person"></i><span class="">ของฉัน</span></a>');

		$ui->add('<sep>');

		$ui->add('<a href="'.url('project/'.$tpid).'" title="รายละเอียดโครงการ"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');
		if (!$isExternalLink) {
			if (projectcfg::enable('ปฏิทิน')) $ui->add('<a href="'.url('project/'.$rs->tpid.'/info.calendar').'" title="ปฏิทินกิจกรรมของโครงการ"><i class="icon -calendar"></i><span>ปฏิทิน</span></a>');
			$ui->add('<a href="'.url('project/'.$tpid.'/info.action').'" title="บันทึกกิจกรรม"><i class="icon -assignment"></i><span>บันทึกกิจกรรม</span></a>');
			$ui->add('<sep>');
		}
		$ui->add('<a href="'.url('project/'.$tpid.'/eval.hia').'" title="แบบประเมิน HIA"><i class="icon -material '.($isEvalHia ? '-green' : '-gray').'">assessment</i><span class="">แบบประเมิน HIA</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.evalform').'" title="แบบประเมิน"><i class="icon -material">assessment</i><span class="">แบบประเมิน</span></a>');
		if (!$isExternalLink) {
			$ui->add('<a href="'.url('project/'.$tpid.'/info.summary').'" title="สรุปโครงการ"><i class="icon -description"></i><span>สรุปโครงการ</span></a>');
			$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');
		}
		if ($isRight) $ui->add('<a href="'.url('project/'.$tpid.'/info.dashboard').'" rel="nofollow"><i class="icon -dashboard"></i><span>แผงควบคุม</span></a>');

		if ($options->showPrint) {
			$ui->add('<sep>');
			$ui->add('<a href="javascript:window.print()"><i class="icon -print"></i><span>พิมพ์</span></a>');
		}
	} else {
		$ui->add('<a href="'.url('project').'"><i class="icon -home"></i><span class="">โครงการ</span></a>');
		$ui->add('<a href="'.url('project/list').'"><i class="icon -list"></i><span class="">รายชื่อโครงการ</span></a>');
	}
	$ret.=$ui->build()._NL;

	return $ret;
}
?>