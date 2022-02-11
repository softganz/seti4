<?php
function org_supplier_menu() {
	$ret .= '<header class="header"><h3>ข้อมูลผู้ผลิต</h3></header>'._NL;

	$ui = new Ui([
		'type' => 'menu',
		'children' => [
			'<a href="'.url('org').'">หน้าหลัก</a>',
			'<a href="'.url('org/supplier').'">รายชื่อผู้ผลิต</a>',
			'<a href="'.url('org/supplier/org').'">กลุ่ม/องค์กร</a>',
			'<a href="'.url('org/supplier/area').'">พื้นที่</a>',
		],
	]);

	$ret .= $ui->build();

	$ret .= '<a class="floating circle add--main" href="'.url('org/supplier/add').'" title="เพิ่มชื่อผู้ผลิตรายใหม่">+</a>';

	$ret .= '<style>
	.add--main {position:fixed;right:20px;bottom:20px;}
	.item td {padding-top:10px; padding-bottom:10px;}
	.sg-dropbox.left>div {right:0px;}
	.floating {width:24px; height:24px; line-height:24px; background-color:#db4437; border:16px #db4437 solid; border-radius:50%;text-align:center;padding:0;}
	.floating:hover {background-color:#E06E55; border-color:#E06E55;}
	</style>';
	return $ret;
}