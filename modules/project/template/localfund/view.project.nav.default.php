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
	$info = $rs->info ? $rs->info : $rs->project;

	$ret = '';

	$isAdmin = $rs->RIGHT & _IS_ADMIN;
	$isRight = $rs->RIGHT & _IS_ACCESS || $info->orgMemberShipType;
	$isEdit = $rs->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_TRAINER);

	$orgShortName = SG\getFirst($rs->info->orgShortName,$rs->project->orgShortName);

	if ($rs->tpid) {
		$ui = new Ui(NULL,'ui-nav -info -sg-text-center');
		if ($orgShortName)
			$ui->add('<a href="'.url('project/'.($orgShortName? 'fund' : 'org').'/'.$rs->orgid).'"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>');

		$ui->add('<sep>');

		$ui->add('<a href="'.url('project/'.$tpid).'" title="รายละเอียดโครงการ"><i class="icon -material">find_in_page</i><span>รายละเอียด</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.paiddoc').'" title="การเงินโครงการ" rel="nofollow"><i class="icon -material">attach_money</i><span>การเงินโครงการ</span></a>');
		$ui->add('<a href="'.url('project/'.$tpid.'/info.action').'" title="บันทึกกิจกรรม"><i class="icon -material">assignment</i><span>กิจกรรม</span></a>');

		$ui->add('<sep>');

		$ui->add('<a href="'.url('project/'.$tpid.'/info.summary').'" title="สรุปโครงการ"><i class="icon -material">assessment</i><span>สรุปโครงการ</span></a>');
		if ($isRight || $info->membershipType) $ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');

		if ($isRight) $ui->add('<a href="'.url('project/'.$tpid.'/info.dashboard').'" rel="nofollow"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>');
		if ($options->showPrint) {
			$ui->add('<sep>');
			$ui->add('<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>');
		}

	$dropUi = new Ui();
	$dropUi->add('<a class="" href="'.url('project/'.$tpid.'/info.tor').'"><i class="icon -material">beenhere</i><span>ข้อตกลงดำเนินการ(TOR)</span></a>');
	if ($isEdit) {
		$dropUi->add('<a class="" href="'.url('project/'.$tpid.'/info.paiddoc').'" rel="nofollow"><i class="icon -material">attach_money</i><span>ใบเบิกเงิน (ใบฎีกา)</span></a>');
		$dropUi->add('<a href="'.url('project/'.$tpid.'/info.summary').'" title="สรุปโครงการ"><i class="icon -material">assessment</i><span>สรุปโครงการ</span></a>');
		$dropUi->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.status').'" data-rel="box" data-width="480"><i class="icon -material">done_all</i><span>ปิดโครงการ</span></a>');
		$dropUi->add('<sep>');
		$dropUi->add('<a class="" href="'.url('project/'.$tpid.'/info.adminreport').'"><i class="icon -material">add_comment</i><span>ความเห็นเจ้าหน้าที่</span></a>');
	}
	if ($info->proposalId)
		$dropUi->add('<a href="'.url('project/develop/'.$tpid).'"><i class="icon -material">pageview</i><span>พัฒนาโครงการ</span></a>');
	//$ret.='</nav>';


		$ret.=$ui->build()._NL;
		$ret.=sg_dropbox($dropUi->build(),'{class:"leftside -no-print -atright"}');

	} else {
		$ui = new Ui(NULL,'ui-nav -info -sg-text-center');
		$ui->add('<a href="'.url('project').'"><i class="icon -material">home</i><span class="-hidden">โครงการ</span></a>');
		$ui->add('<a href="'.url('project/list').'"><i class="icon -material">view_list</i><span class="-hidden">รายชื่อโครงการ</span></a>');
		$ret.=$ui->build();
	}

	return $ret;
}
?>