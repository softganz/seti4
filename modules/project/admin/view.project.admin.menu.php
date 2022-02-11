<?php
function view_project_admin_menu($menu = NULL) {
	$ret .= '<header class="header"><h3>ผู้จัดการระบบ</h3></header>'._NL;

	$subUi = new Ui(NULL, 'ui-menu');
	if ($menu == 'follow') {
		$subUi->add('<a href="'.url('project/admin/prset').'">ชุดโครงการ</a>');
		$subUi->add('<a href="'.url('project/admin/follow',array('o'=>'date','s'=>'2')).'">โครงการใหม่</a>');
		$subUi->add('<a href="'.url('project/admin/follow',array('r'=>'delete')).'">โครงการแจ้งลบ</a>');
		$subUi->add('<a href="'.url('project/admin/meeting').'">บันทึกผู้เข้าร่วมกิจกรรม</a>');
		$subUi->add('<a href="'.url('project/admin/follow/import').'">นำเข้าโครงการ</a>');
	}

	if ($menu == 'member') {
		$subUi->add('<a href="'.url('project/admin/user',array('search'=>'all','o'=>'uid','s'=>'DESC')).'">รายชื่อสมาชิกใหม่</a>');
		$subUi->add('<a href="'.url('project/admin/user/hits').'">Member Hits</a>');
	}

	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a href="'.url('project/admin').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>');
	$ui->add('<a href="'.url('project/admin/user').'"><i class="icon -material">supervised_user_circle</i><span>สมาชิก</span></a>'.($menu == 'member' ? $subUi->build() : ''));
	$ui->add('<a href="'.url('project/admin/org').'"><i class="icon -material">account_balance</i><span>องค์กร/หน่วยงาน</span></a>'.($menu == 'org' ? $subUi->build() : ''));
	$ui->add('<a href="'.url('project/admin/follow').'"><i class="icon -material">directions_run</i><span>โครงการ</span></a>'.($menu == 'follow' ? $subUi->build() : ''));
	$ui->add('<a href="'.url('project/admin/report').'"><i class="icon -material">analytics</i><span>รายงาน</span></a>'.($menu == 'report' ? $subUi->build() : ''));
	$ui->add('<a href="'.url('project/admin/qt').'"><i class="icon -material">how_to_reg</i><span>แบบสอบถาม</span></a>'.($menu == 'qt' ? $subUi->build() : ''));
	$ui->add('<a href="'.url('project/admin/monitor/realtime').'"><i class="icon -material">access_time</i><span>Realtime Monitor</span></a>'.($menu == 'realtime' ? $subUi->build() : ''));
	$ui->add('<a href="'.url('project/admin/repair').'"><i class="icon -material">handyman</i><span>ซ่อมแซมฐานข้อมูล</span></a>');
	$ui->add('<a href="'.url('project/admin/setting').'"><i class="icon -material">settings</i><span>กำหนดค่าระบบ</span></a>');

	$ret .= $ui->build();
	return $ret;
}
?>