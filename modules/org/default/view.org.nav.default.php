<?php
/**
* Org Main Nav Bar
*
* @param
* @return String
*/

$debug = true;

function view_org_nav_default($orgInfo = NULL, $options = '{}') {
	$orgId = $orgInfo->orgid;

	$isEditable = $orgInfo->RIGHT & _IS_EDITABLE;
	$isAddOrg = user_access('create org content');

	$ret = '';

	$ui = new Ui(NULL,'ui-nav -sg-text-center');
	$dropboxUi = new Ui();

	$ui->add('<a href="'.url('org').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>');
	if ($isAddOrg) $ui->add('<a class="" href="'.url('org/my').'"><i class="icon -material">person</i>จัดการองค์กร</a>');

	$ui->add('<sep>');

	if ($orgId) $ui->add('<a href="'.url('org'.($orgId ? '/'.$orgId : '')).'"><i class="icon -material">account_balance</i><span>ข้อมูลองค์กร</span></a>');
	$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'meeting/').'"><i class="icon -material">assignment</i><span>กิจกรรม</span></a>');
	if ($orgId) $ui->add('<a class="" href="'.url('org/'.$orgId.'/mapping').'"><i class="icon -pin"></i>Mapping</a>');
	$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'member').'"><i class="icon -material">people</i><span>สมาชิก</span></a>');
	if (cfg('org.install.pim'))
		$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'pim').'"><i class="icon -material">people</i><span>PIM</span></a>');
	if (cfg('org.install.volunteer'))
		$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'volunteer').'"><i class="icon -material">people</i><span>อาสาสมัคร</span></a>');
	$ui->add('<a href="'.url('org/'.($orgId ? $orgId.'/' : '').'report').'"><i class="icon -material">assessment</i><span>รายงาน</span></a>');

	$ui->add('<sep>');

	if ($orgId) $dropboxUi->add('<a class="" href="'.url('org/'.$orgId).'"><i class="icon -view"></i>รายละเอียดองค์กร</a>');
	if ($orgId && $isEditable) $dropboxUi->add('<a class="sg-action" href="'.url('org/info/api/'.$orgId.'/delete').'" data-rel="notify" data-done="reload:'.url('org/my').'" data-title="ลบองค์กร!!!" data-confirm="คำเตือน: ต้องการลบองค์การ รวมทั้งข้อมูลอื่น ๆ ขององค์กร กรุณายืนยัน?"><i class="icon -delete"></i>ลบองค์กร</a>');
	$dropboxUi->add('<sep>');
	if ($isAddOrg) $dropboxUi->add('<a class="" href="'.url('org/new').'"><i class="icon -addbig"></i><span>เพิ่มองค์กรใหม่</span></a>');
	$dropboxUi->add('<sep>');
	if ($isAddOrg) $dropboxUi->add('<a class="" href="'.url('org/my').'"><i class="icon -view"></i>จัดการองค์กร</a>');
	$dropboxUi->add('<a class="" href="'.url('org/list').'"><i class="icon -view"></i>รายชื่อองค์กรทั้งหมด</a>');


	if (user_access('administrator orgs')) {
		$ui->add('<a href="'.url('org/admin').'"><i class="icon -material">settings</i><span>ผู้จัดการระบบ</span></a>');
	}

	$ret .= $ui->build();

	if ($dropboxUi->count()) $ret .= sg_dropbox($dropboxUi->build('ul'),'{class:"leftside -atright"}');

	return $ret;
}
?>