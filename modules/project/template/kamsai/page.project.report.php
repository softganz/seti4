<?php
/**
 * Send Document Report
 *
 */
function project_report($self) {
	$ret.='<h3>รายงานวิเคราะห์</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('project/report/goodcenter').'">แผนที่ประเมินศูนย์เรียนรู้</a> <img src="/library/img/new.1.gif" alt="new" />');
	$ui->add('<a href="'.url('project/report/goodproject').'">แผนที่ประเมินคุณค่า - นวัตกรรมที่เกิดขึ้นเพื่อการแลกเปลี่ยนเรียนรู้ (โครงการดีแต่ละแนวทางการดำเนินงานอยู่ที่ไหน)</a> <img src="/library/img/new.1.gif" alt="new" />');
	$ui->add('<a href="'.url('project/report/exptran').'">รายงานบันทึกการจ่ายเงิน</a>');
	$ui->add('<a href="'.url('project/report/expgroup').'">รายงานการจ่ายเงินแยกตามหมวด</a>');
	$ui->add('<a href="'.url('project/report/status').'">รายงานจำแนกตามกลุ่มเป้าหมาย</a>');
	$ui->add('<a href="'.url('project/report/xxx').'">รายงานการมีส่วนร่วมของหน่วยงานที่เกี่ยวข้อง</a>');
	$ret.=$ui->build('ul',array('class'=>'card -menu -project-report-menu','id'=>'project-report-menu'));
	$ret.='<br clear="all" />';

	$ret.='<h3>รายงานการติดตามโครงการ</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('project/report/m1late').'">โครงการส่งรายงาน ง.1 ล่าช้า</a>');
	$ui->add('<a href="'.url('project/report/valuation').'">การประเมินแบบมีโครงสร้าง</a>');
	$ui->add('<a href="'.url('project/report/goodproject').'">โครงการดี ๆ อยู่ที่ไหน</a>');
	$ret.=$ui->build('ul',array('class'=>'card -menu -project-report-menu','id'=>'project-report-menu'));
	$ret.='<br clear="all" />';

	$ret.='<h3>รายงานการบันทึกข้อมูล</h3>';
	$ui=new ui();
	$ui->add('<a href="'.url('project/activity','o=modify&i=20').'">รายงานกิจกรรมที่แก้ไขล่าสุด</a>');
	$ui->add('<a href="'.url('project/activity','o=date1&i=20').'">รายงานกิจกรรมที่เกิดขึ้นล่าสุด</a>');
	$ui->add('<a href="'.url('project/activity','o=trid&i=20').'">รายงานกิจกรรมที่ส่งมาล่าสุด</a>');
	$ui->add('<a href="'.url('project/report/s1ready').'">โครงการที่สร้างรายงาน ส.1 แล้ว</a>');
	$ui->add('<a href="'.url('project/report/s2ready').'">โครงการที่สร้างรายงาน ส.2 แล้ว</a>');
	$ui->add('<a href="'.url('project/report/m1ready').'">โครงการที่สร้างรายงาน ง.1 แล้ว</a>');
	$ui->add('<a href="'.url('project/report/estimationready').'">โครงการที่สร้างรายงานประเมินแล้ว</a>');
	$ui->add('<a href="'.url('project/report/followready').'">โครงการที่สร้างรายงานติดตามแล้ว</a>');
	$ret.=$ui->build('ul',array('class'=>'card -menu -project-report-menu','id'=>'project-report-menu'));
	$ret.='<br clear="all" />';

	$ret.='<h3>รายงานอื่น ๆ</h3>';
	$ui=new ui();
	if (projectcfg::enable('trainer')) $ui->add('<a href="'.url('project/report/trainer').'" title="รายชื่อพี่เลี้ยง">รายชื่อพี่เลี้ยง</a>');
	$ui->add('<a href="'.url('project/report/owner').'" title="รายชื่อผู้รับผิดชอบโครงการ">รายชื่อผู้รับผิดชอบโครงการ</a>');
	$ui->add('<a href="'.url('project/report/weightcheck').'">รายงานตรวจสอบการบันทึกข้อมูลภาวะโภชนาการ</a>');
	$ret.=$ui->build('ul',array('class'=>'card -menu -project-report-menu','id'=>'project-report-menu'));
	$ret.='<br clear="all" />';

	return $ret;
}
?>