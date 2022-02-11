<?php
function flood_admin_menu($menu=NULL) {
	$ret = '<header class=""><h3>ผู้จัดการระบบ</h3></header>'._NL;

	$ui = new Ui([
		'type' => 'menu',
		'children' => [
			'<a href="'.url('flood/admin').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
			'<a href="'.url('flood/admin/member').'"><i class="icon -material">group</i><span>สมาชิก</span></a>'.($menu=='member'?$submenu:''),
			'<a href="'.url('ad').'"><i class="icon -material"></i><span>โฆษณา</span></a>',
			'<a href="'.url('flood/admin/setting').'"><i class="icon -material">settings</i><span>อื่น ๆ</span></a>',
		],
	]);

	$ret .= $ui->build();

	return $ret;
}
?>