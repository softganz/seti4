<?php
/**
 * Home page
 *
 * @return String
 */
function saveup_admin($self) {
	R::View('saveup.toolbar',$self,'ระบบงานกลุ่มออมทรัพย์ '.cfg('saveup.version'));
	$self->theme->title='กลุ่มออมทรัพย์ - ระบบจัดการ';
	$ret.='<p>ยินดีต้อนรับ <strong>'.i()->name.'</strong> เข้าสู่ระบบจัดการ</p>';

	$ui=new ui();
	$ui->add('<a href="'.url('saveup/admin/member/changeid').'">เปลี่ยนไอดีสมาชิก</a>');
	$ui->add('<a href="'.url('saveup/admin/member/remove').'">ลบสมาชิก</a>');
	$ret.=$ui->build();
	return $ret;
}
?>