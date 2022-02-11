<?php
/**
* Project :: Fund Report Max Balance
* Created 2018-02-23
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/maxbalance
*/

$debug = true;

// TODO : ยังไม่เริ่มเขียนรายงาน
function project_fund_report_maxbalance($self) {
	$year=post('yr');
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');

	$repTitle='กองทุนที่มีเงินสะสมเหลือมากที่สุด';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/balance').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$ret .= message('', 'อยู่ระหว่างการพัฒนา : รายงานนี้กำลังอยู่ในช่วงระหว่างการพัฒนา กรุณากลับมาดูใหม่อีกครั้ง');
	$ret .= 'รายละเอียด : 	กองทุนที่มีเงินสะสมเหลือมากที่สุด (20 อันดับ) พร้อมความเคลื่อนไหวรายเดือน แสดงเป็นกราฟแนวนอน';
	return $ret;
}
?>
