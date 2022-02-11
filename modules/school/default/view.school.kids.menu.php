<?php
function view_school_kids_menu($orgid) {
	$ret.='<h2>จัดการข้อมูล</h2>';
	$ui=new Ui(NULL,'ui-menu');
	$ui->add('<a class="" href="'.url('school/kids/person/'.$orgid).'"><span>ข้อมูลนักเรียน</span></a>');
	$ui->add('<h3>บันทึกรายคน</h3>');
	$ui->add('<a class="" href="'.url('school/kids/weight/'.$orgid).'"><span>บันทึกน้ำหนัก/ส่วนสูง รายบุคคล</span></a>');
	$ui->add('<a class="" href="'.url('school/kids/eat/'.$orgid).'"><span>บันทึกแบบสอบถามพฤติกรรมการกิน การเคลื่อนไหวทางกาย และออกกำลังกาย รายบุคคล</span></a>');
	$ui->add('<h3>บันทึกรายชั้น</h3>');

	$ui->add('<a class="" href="'.url('school/summary/weight/'.$orgid).'"><span>บันทึกน้ำหนัก/ส่วนสูง รายชั้น</span></a>');
	$ui->add('<a class="" href="'.url('school/summary/eat/'.$orgid).'"><span>บันทึกแบบสอบถามพฤติกรรมการกิน การเคลื่อนไหวทางกาย และออกกำลังกาย รายชั้น</span></a>');
	$ui->add('<sep>');
	$ui->add('<h3>กำหนดค่า</h3>');
	$ui->add('<a class="" href="'.url('school/dashboard/'.$orgid).'"><span>ข้อมูลโรงเรียน</span></a>');
	$ui->add('<a class="" href="'.url('school/dashboard/'.$orgid).'"><span>ข้อมูลผู้ใช้งาน</span></a>');
	$ui->add('<sep>');
	$ui->add('<a class="" href="'.url('school/dashboard/delete/'.$orgid).'"><span>ลบโรงเรียน</span></a>');

	$ret.=$ui->build();
	return $ret;
}
?>