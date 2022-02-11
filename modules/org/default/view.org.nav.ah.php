<?php
function view_org_nav_ah($info = NULL, $options = '{}') {
	$orgId = $info->orgid;

	$ui = new Ui(NULL,'ui-nav -sg-text-center');

	$ui->add('<a href="'.url('org').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('org'.($orgId ? '/'.$orgId : '')).'"><i class="icon -material">description"></i><span>องค์กร</span></a>');
	$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'ah/home').'"><i class="icon -material">view_list</i><span>รายละเอียด</span></a>');
	$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'ah/map').'"><i class="icon -material">place</i><span>แผนที่</span></a>');
	if (user_access('administrator orgs')) {
		$ui->add('<a href="'.url('org/admin').'"><i class="icon -material">settings</i><span>ผู้จัดการระบบ</span></a>');
	}
	
	$ret .= $ui->build();
	return $ret;
}
?>