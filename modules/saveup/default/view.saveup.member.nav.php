<?php
/**
* Saveup Member Navigator
* Created 2018-09-13
* Modify  2019-05-27
*
* @param 
* @return String
*/

$debug = true;

function view_saveup_member_nav($info = NULL, $options = '{}') {
	$ret = '';

	$isAdmin = user_access('administer saveups');

	$dboxUi = new Ui(NULL,'ui-dropbox');

	$ui = new Ui(NULL,'ui-nav -main -sg-text-center');
	$ui->add('<a href="'.url('saveup').'"><i class="icon -home"></i><span>หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('saveup/member').'"><i class="icon -people"></i><span>สมาชิก</span></a>');
	$ui->add('<a href="'.url('saveup/gl').'"><i class="icon -money"></i><span>บัญชี</span></a>');
	$ui->add('<a href="'.url('saveup/report').'"><i class="icon -report"></i><span>รายงาน</span></a>');
	if (user_access('administer saveups')) $ui->add('<a href="'.url('saveup/admin').'"><i class="icon -setting"></i><span>จัดการ</span></a>');

	$ui->add('<sep>');

	//$ui->add('<a href="'.url('saveup/member/list').'"><i class="icon -people"></i><span>สมาชิกปัจจุบัน</span></a>');
	//$ui->add('<a href="'.url('saveup/member/line').'"><i class="icon -people"></i><span>กลุ่มสายสัมพันธ์</span></a>');

	$dboxUi->add('<a href="'.url('saveup/member/list',array('st'=>'all')).'"><i class="icon -people"></i><span>สมาชิกทั้งหมด</span></a>');
	$dboxUi->add('<a href="'.url('saveup/member/list', array('st'=>'inactive')).'"><i class="icon -people -gray"></i><span>สมาชิกพ้นสภาพ</span></a>');

	if (user_access('administrator saveups,create saveup content')) {
		$ui->add('<sep>');
		if ($info->mid) {
			$ui->add('<a href="'.url('saveup/member/view/'.$info->mid).'"><i class="icon -material">person</i><span>รายละเอียด</span></a>');
			$ui->add('<a href="'.url('saveup/gl/card/'.$info->mid).'"><i class="icon -material">receipt</i><span>สมุดคุมยอด</span></a>');
			//$ui->add('<a href="'.url('saveup/member/'.$info->mid.'/service').'"><i class="icon -description"></i><span>บริการ</span></a>');
		}
	}
	$ret .= $ui->build()._NL;


	$ret .= sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return $ret;
}
?>