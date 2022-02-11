<?php
function view_saveup_loan_nav($info = NULL, $options = '{}') {
	$ret = '';

	$isAdmin = user_access('administer saveups');

	$ui = new ui(NULL,'ui-nav');
	$dboxUi = new Ui(NULL,'ui-dropbox');

	$ui = new Ui(NULL,'ui-nav -main -sg-text-center');
	$ui->add('<a href="'.url('saveup').'"><i class="icon -home"></i><span>หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('saveup/member').'"><i class="icon -people"></i><span>สมาชิก</span></a>');
	$ui->add('<a href="'.url('saveup/gl').'"><i class="icon -money"></i><span>บัญชี</span></a>');
	$ui->add('<a href="'.url('saveup/report').'"><i class="icon -report"></i><span>รายงาน</span></a>');
	if (user_access('administer saveups')) $ui->add('<a href="'.url('saveup/admin').'"><i class="icon -setting"></i><span>จัดการ</span></a>');

	$ui->add('<sep>');
	$ui->add('<a href="'.url('saveup/loan').'"><i class="icon -material">view_list</i><span>ใบกู้เงิน</span></a>');
	if ($info->loanno) {
		$ui->add('<sep>');
		$ui->add('<a href="'.url('saveup/loan/view/'.$info->loanno).'"><i class="icon -material">description</i><span>รายละเอียด</span></a>');
		$ui->add('<a href="'.url('saveup/loan/rcv','id='.$info->loanno).'"><i class="icon -material">money</i><span>รับชำระหนี้</span></a>');
		$ui->add('<a href="#" onclick="window.print(); return false" title="พิมพ์ใบกู้เงิน" class="print-button"><i class="icon -print"></i><span>พิมพ์</span></a>');

		$dboxUi->add('<a href="'.url('saveup/loan/edit/'.$info->loanno).'"><i class="icon -material">edit</i><span>แก้ไข</span></a>');
		$dboxUi->add('<a href="'.url('saveup/loan/cancel/'.$info->loanno).'"><i class="icon -material">cancel</i><span>ยกเลิกใบกู้เงิน</span></a>');
		$dboxUi->add('<sep>');
	}

	$dboxUi->add('<a href="'.url('saveup/loan', array('paided' => 'yes')).'"><i class="icon -material">list</i>ใบกู้เงินชำระแล้ว</a>');
	$dboxUi->add('<a href="'.url('saveup/loan', array('paided' => 'all')).'"><i class="icon -material">list</i>ใบกู้เงินทั้งหมด</a>');

	$ret .= $ui->build()._NL;


	if ($dboxUi->count()) $ret .= sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return $ret;
}
?>