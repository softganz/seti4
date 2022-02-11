<?php
function view_saveup_nav($info=NULL,$options='{}') {
	$ret='';

	$isAdmin=user_access('administer saveups');

	$ui=new ui(NULL,'ui-nav');
	$dboxUi=new Ui(NULL,'ui-dropbox');

	$ui = new Ui(NULL,'ui-nav -main -sg-text-center');
	$ui->add('<a href="'.url('saveup').'"><i class="icon -home"></i><span>หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('saveup/member').'"><i class="icon -people"></i><span>สมาชิก</span></a>');
	$ui->add('<a href="'.url('saveup/gl').'"><i class="icon -money"></i><span>บัญชี</span></a>');
	$ui->add('<a href="'.url('saveup/report').'"><i class="icon -report"></i><span>รายงาน</span></a>');
	if (user_access('administer saveups')) $ui->add('<a href="'.url('saveup/admin').'"><i class="icon -setting"></i><span>จัดการ</span></a>');

	/*
	$dboxUi->add('<a class="" href="'.url('project/idea').' " title="Project Concept Paper"><i class="icon -list"></i><span>รวมแนวคิด</span></a>');

	if ($info->tpid) {
		$ui->add('<a class="" href="'.url('project/idea/view/'.$info->tpid).'"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');
		if ($info->proposalId) $ui->add('<a class="" href="'.url('project/develop/view/'.$info->tpid).'"><i class="icon -viewdoc"></i><span>พัฒนาโครงการ</span></a>');
		if ($info->followId) $ui->add('<a class="" href="'.url('paper/'.$info->tpid).'"><i class="icon -viewdoc"></i><span>ติดตามโครงการ</span></a>');
		$ui->add('<a class="" href="javascript:window.print()"><i class="icon -print"></i><span style="display:hidden">พิมพ์</span></a>');
		if ($isAdmin) {
			if (empty($info->proposalId)) {
				$dboxUi->add('<a class="sg-action" href="'.url('project/idea/view/'.$info->tpid.'/todev').'" data-confirm="ต้องการสร้างโครงการพัฒนาจากแนวคิดนี้ กรุณายืนยัน?"><i class="icon -adddoc"></i><span>สร้างเป็นโครงการพัฒนา</span></a>');
			}
		}
	}
	*/

	$ret.=$ui->build()._NL;

	if ($dboxUi->count()) $ret.=sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return $ret;
}
?>