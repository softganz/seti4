<?php
function view_saveup_rcv_nav($info = NULL, $options = '{}') {
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

	$ui->add('<a href="'.url('saveup/rcv').'"><i class="icon -money"></i><span>ใบรับเงิน</span></a>');

	if ($info->rcvno) {
		$ui->add('<sep>');
		$ui->add('<a href="'.url('saveup/rcv/'.$info->rcvid).'"><i class="icon -description"></i><span>รายละเอียด</span></a>');
		$ui->add('<a href="#" onclick="window.print(); return false" title="พิมพ์ใบรับเงิน" class="print-button"><i class="icon -print"></i><span>พิมพ์</span></a>');

		$dboxUi->add('<a class="sg-action" href="'.url('saveup/rcv/'.$info->rcvid.'/view/edit').'" data-rel="#main"><i class="icon -material">edit</i><span>แก้ไข</span></a>');
		$dboxUi->add('<sep>');
		$dboxUi->add('<a href="'.url('saveup/rcv/'.$info->rcvid.'/cancel').'"><i class="icon -material">cancel</i><span>ยกเลิกใบรับเงิน</span></a>');
	}

	$ret .= $ui->build()._NL;


	if ($dboxUi->count()) {
		$ret .= sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');
	}

	return $ret;
}
?>