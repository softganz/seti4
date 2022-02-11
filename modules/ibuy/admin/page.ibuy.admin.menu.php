<?php
function ibuy_admin_menu($menu = NULL) {
	$ret .= '<header class="header"><h3>ผู้จัดการระบบ</h3></header>'._NL;


	$ret .= '<nav class="nav">';

	$ui = new Ui(NULL, 'ui-menu');
	$subUi = new Ui(NULL, 'ui-menu');

	if ($menu == 'product') {
		$subUi = new Ui(NULL, 'ui-menu');
		$subUi->add('<a href="'.url('ibuy/admin/product',array('st'=>'N')).'">สินค้าทำงาน</a>');
		$subUi->add('<a href="'.url('ibuy/admin/product',array('t'=>'new')).'">สินค้าใหม่</a>');
		$subUi->add('<a href="'.url('ibuy/admin/product',array('st'=>'O')).'">สินค้าหมด</a>');
		$subUi->add('<a href="'.url('ibuy/admin/product',array('st'=>'Y')).'">สินค้าเลิกจำหน่าย</a>');
	}

	if ($menu == 'member') {
		$subUi->add('<a href="'.url('ibuy/admin/cart').'">ตะกร้า</a>');
	}

	$ui->add('<a href="'.url('ibuy/admin').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('ibuy/admin/order').'"><i class="icon -material">euro_symbol</i><span>คำสั่งซื้อ</span></a>');
	$ui->add('<a href="'.url('ibuy/admin/order').'"><i class="icon -material">attach_money</i><span>แจ้งชำระเงิน</span></a>');
	$ui->add('<a href="'.url('ibuy/customer').'"><i class="icon -material">person_pin</i><span>ศูนย์บริการลูกค้า</span></a>');

	$ui->add('<a href="'.url('ibuy/admin/product').'"><i class="icon -material">local_florist</i><span>สินค้า</span></a>'.($menu == 'product' ? $subUi->build() : ''));
	$ui->add('<a href="'.url('ibuy/admin/member').'"><i class="icon -material">people</i><span>สมาชิก</span></a>'.($menu == 'member' ? $subUi->build() : ''));
	$ui->add('<a href="'.url('ibuy/admin/category').'"><i class="icon -material">category</i><span>หมวดสินค้า</span></a>');
	if (user_access('access ibuys report')) $ui->add('<a href="'.url('ibuy/admin/report').'"><i class="icon -material">assignment</i><span>รายงาน</span></a>');
	if (user_access('access ibuys admin report')) $ui->add('<a href="'.url('ibuy/admin/adminreport').'"><i class="icon -material">assignment</i><span>รายงานผู้จัดการระบบ</span></a>');
	$ui->add('<a href="'.url('ad').'"><i class="icon -material"></i><span>โฆษณา</span></a>');
	$ui->add('<a href="'.url('ibuy/admin/setting').'"><i class="icon -material"></i><span>อื่น ๆ</span></a>');

	$ret .= $ui->build();

	$ret .= '</nav>';

	return $ret;
}
?>