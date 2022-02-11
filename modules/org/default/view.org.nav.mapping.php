<?php
function view_org_nav_mapping($info = NULL, $options = '{}') {
	$orgId = $info->orgid;

	$ui = new Ui(NULL,'ui-nav -sg-text-center');

	$ui->add('<a href="'.url('org').'"><i class="icon -home"></i><span>หน้าหลัก</span></a>');
	$ui->add('<a class="" href="'.url('org/my').'"><i class="icon -material">person</i>จัดการองค์กร</a>');

	$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'mapping').'"><i class="icon -material">list</i><span>รายชื่อ</span></a>');
	$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'mapping.map').'"><i class="icon -material">place</i><span>แผนที่</span></a>');
	$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'mapping.search').'"><i class="icon -material">search</i><span>ค้นหา</span></a>');
	if (user_access('administrator orgs')) {
		$ui->add('<a href="'.url('org/admin').'"><i class="icon -material">settings</i><span>ผู้จัดการระบบ</span></a>');
	}
	
	$ret .= $ui->build();
	return $ret;
}
?>