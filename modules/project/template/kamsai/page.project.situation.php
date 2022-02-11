<?php
/**
* Project situation
*
* @param Object $self
* @return String
*/
function project_situation($self) {
	project_model::set_toolbar($self,'สถานการณ์โครงการ');

	$ui=new ui('','-project -situation');
	$ui->add('<a href="'.url('project/situation/weight').'">สรุปข้อมูลภาวะโภชนาการ - จำแนกตามชั้นแรียน</a>');
	$ui->add('<a href="'.url('project/report/weightbyschool').'">สรุปข้อมูลภาวะโภชนาการ (น้ำหนักตามเกณฑ์ส่วนสูง) - จำแนกตามโรงเรียน</a> <img src="/library/img/new.1.gif" />');
	$ui->add('<a href="'.url('project/report/heightbyschool').'">สรุปข้อมูลภาวะโภชนาการ (ส่วนสูงตามเกณฑ์อายุ) - จำแนกตามโรงเรียน</a> <img src="/library/img/new.1.gif" />');
	$ui->add('<sep>');
	$ui->add('<a href="'.url('project/situation/eat').'">สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</a>');
	$ui->add('<a href="">ข้อมูลการสำรวจสถานการณ์การกินอาหารและออกกำลังกายของนักเรียนโดย สอส.</a>');
	$ui->add('<sep>');
	$ui->add('<a href="'.url('project/report/checkweightinput').'">ตรวจสอบบันทึกสถานการณ์ภาวะโภชนาการ - การบันทึกของโรงเรียนแต่ละเทอม/ครั้งที่</a> <img src="/library/img/new.1.gif" />');
	$ui->add('<a href="'.url('project/report/weightcheck').'">ตรวจสอบบันทึกสถานการณ์ภาวะโภชนาการ - จำนวนนักเรียนผิดพลาด</a>');
	$ui->add('<a href="'.url('project/situation/list').'">สถานะการบันทึกข้อมูลรายงานสถานการณ์ภาวะโภชนาการนักเรียน</a>');

	$ret.=$ui->build(NULL,'-main');
	return $ret;
}
?>