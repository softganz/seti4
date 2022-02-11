<?php
function org_seedfund_admin($self) {
	$self->theme->title='บริหาร - กองทุนเมล็ดพันธุ์';
	R::Page('org.seedfund.toolbar',$self);

	$ui=new Ui();
	$ui->add('<a href="">ฐานรายชื่อเมล็ดพันธุ์</a>');
	$ui->add('<a href="">ฐานรายชื่อผู้ต้องการ</a>');
	$ui->add('<a href="">ฐานรายชื่อผู้บริจาค</a>');
	$ui->add('<a href="">รายการรับจ่ายเมล็ดพันธุ์ - บันทึกรับ - บันทึกจ่าย</a>');
	$ui->add('<a href="">Stock card เมล็ดพันธุ์ - ยอดยกมา - ยอดคงเหลือ - ยอดรับ - ยอดจ่าย - รายการรับจ่าย</a>');
	$ret.=$ui->build();
	return $ret;
}