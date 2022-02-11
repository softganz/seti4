<?php
function view_saveup_bank_nav($info=NULL,$options='{}') {
	$ret='';

	$isAdmin=user_access('administer saveups');

	$ui=new Ui(NULL,'ui-nav -info');
	$dboxUi=new Ui(NULL,'ui-dropbox');

	$ui->add('<a href="'.url('saveup/bank').'">หน้าหลัก</a>');
	$ui->add('<a class="sg-action" href="'.url('saveup/bank/member').'" data-rel="saveup-main">สมาชิก</a>');
	$ui->add('<a class="sg-action" href="'.url('saveup/bank/trans').'" data-rel="saveup-main" id="saveup-bank-menu-trans">รายการฝาก-ถอน</a>');

	if (user_access('administer saveups')) $ui->add('<a href="'.url('saveup/bank/setting').'">จัดการ</a>');

	$ret.=$ui->build()._NL;

	$ret.=sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return $ret;
}
?>