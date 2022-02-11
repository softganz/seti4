<?php
function imed_app_poorman_admin($self) {
	R::View('imed.toolbar',$self,'ผู้จัดการระบบ','app.poorman');

	$isAdmin = user_access('admin');

	if (!$isAdmin) return message('error', 'access denied');


	$ui=new Ui(NULL,'ui-menu -main -poorman');
	$ui->add('<a class="sg-action btn -primary -fill" href="'.url('imed/app/poorman/admin/memberqt').'" data-webview="แบบสอบถามของสมาชิก"><i class="icon -people"></i><span>แบบสอบถามของสมาชิก</span></a>');
	$ui->add('<a class="sg-action btn -primary -fill" href="'.url('imed/app/poorman/admin/summary').'" data-webview="จำนวนแแบบสอบถาม"><i class="icon -people"></i><span>จำนวนแแบบสอบถาม</span></a>');
	$ui->add('<a class="sg-action btn -primary -fill" href="'.url('imed/app/poorman/admin/cleardata').'" data-webview="CLEAR EMPTY DATA!!!"><i class="icon -cancel"></i><span>CLEAR EMPTY DATA!!!</span></a>');
	//$ui->add('<a class="btn -primary -fill" href="'.url('imed/app/poorman/admin/export').'"><i class="icon -report -white"></i><span>ส่งออกข้อมูล</span></a>');
	$ret.=$ui->build();
	return $ret;
}
?>