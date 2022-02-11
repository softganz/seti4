<?php
function ibuy_admin_report($self) {
	$self->theme->title='รายงานผู้จัดการระบบ';
	$self->theme->sidebar=R::Page('ibuy.admin.menu','report');

	if (!user_access('access ibuys report')) return message('error','access denied');

	$ret.='<h3>รายงาน</h3>';
	$ui=new ui();

	if (cfg('ibuy.price.franchise')) {
		$ui->add('<a href="'.url('ibuy/report/sale/thismonth').'">รายงานการคำนวณส่วนลดและค่าการตลาดประจำเดือน</a>');
		$ui->add('<a href="'.url('ibuy/report/discount').'">รายงานการคำนวณส่วนลด</a>');
	}
	$ret.=$ui->build('ul');

	if (user_access('access administrator pages')) {
		$ui=new ui();
		$ret.='<h3>รายงานผู้จัดการระบบ</h3>';
		$ui->add('<a href="'.url('ibuy/admin/report/customerbuy').'">รายงานยอดซื้อสินค้า</a>');
		$ui->add('<a href="'.url('ibuy/admin/report/totalsale').'">รายงานยอดขายสินค้า - แยกตามช่วงเวลา</a>');

		$ret.=$ui->build('ul');
		$ret.='<hr />';

		$ui=new ui();
		$ui->add('<a href="'.url('ibuy/admin/report/order').'">รายงานใบสั่งซื้อ</a>');
		$ui->add('<a href="'.url('ibuy/report/bestseller').'">50 อันดับสินค้าขายดี</a>');
		$ui->add('<a href="'.url('ibuy/report/totalsale/byproduct').'">รายงานยอดขายสินค้า - แยกตามชื่อสินค้า</a>');
		$ui->add('<a href="'.url('ibuy/report/noavailable').'">รายงานสินค้างดจำหน่าย</a>');

		$ret.=$ui->build('ul');
	}

	return $ret;
}
?>