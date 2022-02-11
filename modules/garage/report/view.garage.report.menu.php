<?php
function view_garage_report_menu() {
	$ret .= '<h3>ใบสั่งซ่อม</h3>';

	$ui = new Ui(NULL,'ui-menu');

	$ui->add('<a href="'.url('garage/report/datetoreturn').'">วันที่นัดรับรถ</a>');
	$ui->add('<a href="'.url('garage/report/jobbymonth').'">จำนวนใบสั่งซ่อมประจำเดือน</a>');
	$ui->add('<a href="'.url('garage/report/joblist').'">รายงานใบสั่งซ่อม</a>');
	$ui->add('<a href="'.url('garage/report/jobcarin').'">รายงานวันรถเข้า</a>');
	$ui->add('<a href="'.url('garage/report/jobcardate').'">รายงานวันนัดรับรถ</a>');
	$ui->add('<a href="'.url('garage/report/jobcarnotret').'">รายงานรถที่ยังซ่อมอยู่</a>');
	$ui->add('<a href="'.url('garage/report/jobwait').'">รายงานรถรอซ่อม</a>');
	$ui->add('<a href="'.url('garage/report/jobcarret').'">รายงานวันคืนรถ</a>');
	$ui->add('<a href="'.url('garage/report/jobstatus').'">รายงานสถานะใบสั่งซ่อม</a>');
	$ui->add('<a href="'.url('garage/report/wage').'">รายงานค่าแรง</a>');

	$ret .= $ui->build();

	$ret .= '<h3>การเงิน</h3>';

	$ui = new Ui(NULL,'ui-menu');

	$ui->add('<a href="'.url('garage/report/jobnotmoney').'">ใบสั่งซ่อมยังไม่รับเงิน</a>');
	$ui->add('<a href="'.url('garage/report/jobgetmoney').'">ใบสั่งซ่อมรับเงินแล้ว</a>');

	$ui->add('<a href="'.url('garage/report/jobnotreply').'">ใบเสนอราคายังไม่ตกลงราคา</a>');

	$ui->add('<a href="'.url('garage/report/billnotmoney').'">รายงานใบวางบิลยังไม่รับเงิน</a>');
	
	$ui->add('<a href="'.url('garage/report/jobrcvmoney').'">การรับเงิน</a>');
	$ui->add('<a href="'.url('garage/report/apmastnotmoney').'">ใบรับของค้างจ่าย</a>');
	$ui->add('<a href="'.url('garage/report/recieve').'">ใบเสร็จรับเงิน</a>');

	$ret .= $ui->build();
	return $ret;
}
?>