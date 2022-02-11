<?php
/**
* Project Dashboard Meenu
*
* @param
* @return String
*/

$debug = true;

function view_project_dashboard_menu($projectInfo) {
	$projectId = $projectInfo->tpid;

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isRight = $projectInfo->RIGHT & _IS_ACCESS;
	$isEdit = $projectInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_TRAINER);
	$isDeletable = $isAdmin || (i()->ok && i()->uid == $projectInfo->info->uid);

	$ret = '';

	$ret.='<header class="header"><h3>แผงควบคุม</h3></header>'._NL;
	if ($projectId) {
		$ui = new Ui(NULL, 'ui-menu');
		$ui->add('<a href="'.url('project/'.$projectId).'"><i class="icon -material">find_in_page</i><span>รายละเอียดโครงการ</span></a>');
		$ui->add('<a href="'.url('project/'.$projectId.'/info.calendar').'"><i class="icon -material">event</i><span>ปฏิทินกิจกรรมโครงการ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');
		//$ui->add('<a href="'.url('paper/'.$projectId.'/edit/doc').'">ไฟล์รายละเอียดโครงการ</a>');
		//$ui->add('<a href="'.url('paper/'.$projectId.'/owner/budget').'">การเงิน</a>');
		$ui->add('<sep>');

		if ($isAdmin) {
			$statusIcon = array('กำลังดำเนินโครงการ'=>'done','ดำเนินการเสร็จสิ้น'=>'done_all','ยุติโครงการ'=>'cancel','ระงับโครงการ'=>'block');
			$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.status').'" data-rel="box" data-width="480"><i class="icon -material">'.$statusIcon[$projectInfo->info->project_status].'</i><span>สถานะ:'.$projectInfo->info->project_status.'</span></a>');
		}

		if ($isDeletable) {
			$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.delete').'" data-rel="box" data-width="640"><i class="icon -material">delete</i><span>ลบโครงการ</span></a>');
		}
		/*
		if (user_access('administer projects') || i()->uid==$topic->uid) {
			if ($topic->project->project_statuscode==1) {
				$ui->add('<a href="'.url('paper/'.$projectId.'/owner/complete').'">แจ้งปิดโครงการ - โครงการดำเนินการเสร็จสิ้นสมบูรณ์</a>');
			} else if ($topic->project->project_status=='ดำเนินการเสร็จสิ้น' && $projectInfo->info->flag==_PUBLISH) {
				$ui->add('<a href="'.url('paper/'.$projectId.'/owner/complete','cancelreport=yes').'">ยกเลิกการแจ้งปิดโครงการ</a>');
			}
			if ($topic->project->project_statuscode==1) {
				$ui->add('<a href="'.url('paper/'.$projectId.'/owner/cancel').'">แจ้งยุติโครงการ - โครงการถูกยุติหลังจากดำเนินการไประยะหนึ่ง</a>');
			} else if ($topic->project->project_status=='ยุติโครงการ' && $projectInfo->info->flag==_PUBLISH) {
				$ui->add('<a href="'.url('paper/'.$projectId.'/owner/cancel','cancelreport=yes').'">ยกเลิกการแจ้งยุติโครงการ</a>');
			}
			if ($topic->project->project_status=='กำลังดำเนินโครงการ') {
				$ui->add('<a href="'.url('paper/'.$projectId.'/owner/delete').'">แจ้งลบโครงการ - ลบโครงการออกจากระบบติดตาม</a>');
			} else if ($topic->project->project_status=='ระงับโครงการ') {
				$ui->add('<a href="'.url('paper/'.$projectId.'/owner/delete','cancelreport=yes').'">ยกเลิกการแจ้งลบโครงการ</a>');
			}
		}
		if (user_access('administer projects') && $topic->project->project_status=='ระงับโครงการ' && $projectInfo->info->flag==_DRAFT) {
			$ui->add('<a href="'.url('paper/'.$projectId.'/edit/delete').'">ลบโครงการ</a>');
		}
		*/
		$ret.='<h4>โครงการ</h4>'.$ui->build('ul');

		$ui = new Ui(NULL, 'ui-menu');
		$ui->add('<a href="'.url('project/'.$projectId.'/info.action').'"><i class="icon -material">assignment</i><span>บันทึกกิจกรรม</span></a>');

		$ui->add('<a href="'.url('project/'.$projectId.'/info.adminreport').'"><i class="icon -material">assignment</i><span>บันทึกเจ้าหน้าที่</span></a>');

		if ($isEdit) {
			$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.register').'" data-rel="#main" data-done="moveto:0,0"><i class="icon -material">people</i><span>ใบลงทะเบียน</span></a>');
			$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/join').'" data-rel="#main" data-done="moveto:0,0"><i class="icon -material">monetization_on</i><span>ใบสำคัญรับเงิน</span></a>');
			if ($calid) {
				$ui->add('<a href="'.url('project/'.$projectId.'/info.join/'.$calid).'">บันทึกผู้เข้าร่วมกิจกรรม</a>');
			}
			$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.joins').'" data-rel="#main"><i class="icon -material">people</i><span>รายชื่อผู้เข้าร่วมกิจกรรม</span></a>');
		}
		$ret.='<h4>กิจกรรม</h4>'.$ui->build('ul');

		$ui = new Ui(NULL, 'ui-menu');
		$ui->add('<a href="'.url('project/'.$projectId.'/operate').'"><i class="icon -material">attach_money</i><span>รายงานการเงินประจำงวด</span></a>');
		$ui->add('<a href="'.url('project/'.$projectId.'/operate').'"><i class="icon -material">attach_money</i><span>รายงานสรุปการเงินโครงการ</span></a>');
		$ui->add('<a href="'.url('project/'.$projectId.'/operate').'"><i class="icon -material">pageview</i><span>รายงานผลงานประจำงวด</span></a>');
		$ui->add('<a href="'.url('project/'.$projectId.'/info.summary').'"><i class="icon -material">pageview</i><span>รายงานสรุป</span></a>');
		$ui->add('<a href="'.url('project/'.$projectId.'/finalreport').'"><i class="icon -material">pageview</i><span>รายงานโครงการฉบับสมบูรณ์</span></a>');

		$ret.='<h4>รายงาน</h4>'.$ui->build('ul');

		$ui = new Ui(NULL, 'ui-menu');
		$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/page.setting').'" data-rel="#main"><i class="icon -material">settings</i><span>กำหนดค่า</span></a>');
		//$ui->add('<a href="'.url('paper/'.$projectId.'/edit').'"><i class="icon -material">settings</i><span>จัดการหัวข้อ</span></a>');
		$ret.='<h4>Admin</h4>'.$ui->build('ul');
	}

	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a href="'.url('project/my').'"><i class="icon -material">person</i><span>โครงการในความรับผิดชอบ</span></a>');
	//$ui->add('<a href="'.url('paper/post/project').'">เพิ่มโครงการติดตาม</a>');
	$ret.='<h4>อื่น ๆ</h4>'.$ui->build('ul');
	return $ret;
}
?>