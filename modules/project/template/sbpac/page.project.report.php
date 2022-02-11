<?php

/**
 * Send Document Report
 *
 */
function project_report($self) {
	$self->theme->title='รายงาน';
	$ui=new ui();
	$ui->add('<a href="'.url('project/org').'">ภาพรวมโครงการขององค์กร</a>');
	$ui->add('<a href="'.url('project/report/area').'">รายชื่อโครงการแยกตามพื้นที่ดำเนินการ</a>');
	$ui->add('<a href="'.url('project/report/activity').'">รายงานกิจกรรมแยกตามพื้นที่ดำเนินการ</a>');
	$ui->add('<a href="'.url('project/report/subproject').'">รายงานกิจกรรมแผนงาน/ชุดโครงการ</a> <img src="/library/img/new.1.gif" />');
	$ui->add('<a href="'.url('project/report/owner').'">รายงานผู้รับผิดชอบโครงการแยกตามพื้นที่</a>');
	$ui->add('<a href="'.url('project/report/depart').'">รายชื่อโครงการแยกตามสำนัก</a>');
	$ui->add('<a href="'.url('project/report/budget').'">รายชื่อโครงการแยกตามงบการเงิน</a>');
	$ui->add('<a href="'.url('project/report/govplan').'">รายงานโครงการแยกตามความสอดคล้องตามแผนปฏิบัติการแก้ไขปัญหาและพัฒนาของรัฐบาล</a>');
	$ui->add('<a href="'.url('project/report/southplan').'">รายงานโครงการแยกตามความสอดคล้องกับยุทธศาสตร์และแผนปฏิบัติการพัฒนาจังหวัดชายแดนภาคใต้</a>');
	$ui->add('<a href="'.url('project/report/kpi').'">ความสอดคล้องกับตัวชี้วัดแผนงานการแก้ปัญหาจังหวัดชายแดนภาคใต้</a>');
	$ret.='<h3>รายละเอียดโครงการ</h3>'.$ui->build('ul',array('class'=>'ui-card','id'=>'project-report-menu'));

	$ui=new ui();
	$ui->add('<a href="'.url('project/report/my').'">ใบส่งงาน</a>');
	$ui->add('<a href="'.url('project/report/monthly').'">รายงานประจำเดือน</a>');
	$ui->add('<a href="'.url('project/report/monthprocess').'">แบบรายงาน จชต.</a>');
	$ui->add('<a href="'.url('project/activity','o=modify&i=20').'">รายงานกิจกรรมที่แก้ไขล่าสุด</a>');
	$ui->add('<a href="'.url('project/activity','o=date1&i=20').'">รายงานกิจกรรมที่เกิดขึ้นล่าสุด</a>');
	$ui->add('<a href="'.url('project/activity','o=trid&i=20').'">รายงานกิจกรรมที่ส่งมาล่าสุด</a>');
	$ret.='<h3>การติดตาม</h3>'.$ui->build('ul',array('class'=>'ui-card','id'=>'project-report-menu'));

	$ui=new ui();
	$ui->add('อยู่ระหว่างดำเนินการ');
	$ret.='<h3>การประเมิน</h3>'.$ui->build('ul',array('class'=>'ui-card','id'=>'project-report-menu'));

	$ui=new ui();
	if (projectcfg::enable('trainer')) $ui->add('<a href="'.url('project/report/trainer').'" title="รายชื่อพี่เลี้ยง">รายชื่อพี่เลี้ยง</a>');
	$ui->add('<a href="'.url('project/report/owner').'" title="รายชื่อผู้รับผิดชอบโครงการ">รายชื่อผู้รับผิดชอบโครงการ</a>');
	$ret.='<h3>อื่น ๆ</h3>'.$ui->build('ul',array('class'=>'ui-card','id'=>'project-report-menu'));

	if (user_access('administer projects')) {
		$ui=new ui();
		$ui->add('<a href="'.url('project/report/todelete').'">รายชื่อโครงการแจ้งลบ</a>');
		$ret.='<h3>รายงานสำหรับผู้จัดการระบบ</h3>'.$ui->build('ul',array('class'=>'ui-card','id'=>'project-report-menu'));
	}
	return $ret;
}
?>