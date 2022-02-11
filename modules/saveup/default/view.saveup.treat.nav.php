<?php
function view_saveup_treat_nav($info=NULL,$options='{}') {
	$ret='';

	$isAdmin=user_access('administer saveups');

	$dboxUi=new Ui(NULL,'ui-dropbox');

	$ui = new Ui(NULL,'ui-nav -main -sg-text-center');
	$ui->add('<a href="'.url('saveup').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('saveup/member').'"><i class="icon -material">people</i><span>สมาชิก</span></a>');
	$ui->add('<a href="'.url('saveup/gl').'"><i class="icon -material">attach_money</i><span>บัญชี</span></a>');
	$ui->add('<a href="'.url('saveup/report').'"><i class="icon -material">insights</i><span>รายงาน</span></a>');
	if (user_access('administer saveups')) $ui->add('<a href="'.url('saveup/admin').'"><i class="icon -material">settings</i><span>จัดการ</span></a>');

	$ui->add('<sep>');

	$ui->add('<a href="'.url('saveup/treat/list').'"><i class="icon -material">money</i><span>ค่ารักษาพยาบาล</span></a>');
	$ui->add('<a href="'.url('saveup/treat/post').'"><i class="icon -material">post_add</i><span>บันทึกรายการเบิก</span></a>');

	if ($info->tid) {
		$ui->add('<sep>');
		$ui->add('<a href="'.url('saveup/treat/view/'.$info->tid).'"><i class="icon -description"></i><span>รายละเอียด</span></a>');
		$ui->add('<a href="'.url('saveup/treat/modify/'.$info->tid).'"><i class="icon -edit"></i><span>แก้ไข</span></a>');
	}

	$ret.=$ui->build()._NL;

	$ret.=sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return $ret;
}
?>