<?php
/**
* Project :: Follow Dashboard Information
* Created 2021-06-08
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.dashboard
*/

import('widget:project.info.appbar.php');

class ProjectInfoDashboard extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		$projectId = $this->projectId;
		$calid = post('calid');

		$isAdmin = $this->projectInfo->RIGHT & _IS_ADMIN;
		$isRight = $this->projectInfo->RIGHT & _IS_ACCESS;
		$isEdit = $this->projectInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_TRAINER);
		$isDeletable = $isAdmin || (i()->ok && i()->uid == $this->projectInfo->info->uid);

		// $dashboardUi = new Ui(NULL, '-project-dashboard');
		// $dashboardUi->addConfig('nav', '{class: "nav -app-menu"}');

		// $dashboardUi->header('<h3>ข้อมูลโครงการ</h3>');

		// if ($isAdmin) {
		// 	$statusTextList = [
		// 		'กำลังดำเนินโครงการ' => ['text' => 'โครงการกำลังอยู่ในระหว่างดำเนินงาน', 'icon' => 'directions_run'],
		// 		'ดำเนินการเสร็จสิ้น' => ['text' => 'โครงการได้ดำเนินการเรียบร้อยแล้ว พร้อมทั้งส่งรายงานการเงินและผลการดำเนินงานเสร็จสมบูรณ์', 'icon' => 'verified'],
		// 		'ยุติโครงการ' => ['text' => 'เจ้าของโครงการไม่สามารถดำเนินงานต่อไปได้ เจ้าของโครงการจึงขอหยุดการดำเนินงาน', 'icon' => 'pan_tool'],
		// 		'ระงับโครงการ' => ['text' => 'โครงการมีปัญหาในการดำเนินงาน ผู้ให้ทุนโครงการจึงขอระงับไม่ให้ดำเนินงานต่อ', 'icon' => 'block'],
		// 	];
		// 	$dashboardUi->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.status').'" data-rel="box" data-width="480" data-tooltip="'.$statusTextList[$this->projectInfo->info->project_status]['text'].'"><i class="icon -material">'.$statusTextList[$this->projectInfo->info->project_status]['icon'].'</i><span>'.$this->projectInfo->info->project_status.'</span></a>');
		// }

		// $dashboardUi->add('<a href="'.url('project/'.$projectId).'"><i class="icon -material">find_in_page</i><span>รายละเอียด</span></a>');
		// $dashboardUi->add('<a href="'.url('project/'.$projectId.'/info.calendar').'"><i class="icon -material">event</i><span>ปฏิทินกิจกรรม</span></a>');
		// $dashboardUi->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>');

		// $dashboardUi->add('<a href="'.url('project/'.$projectId.'/info.link').'"><i class="icon -material">link</i><span>เชื่อมโยง</span></a>');

		// $dashboardUi->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.org.co').'" data-rel="box" data-width="full"><i class="icon -material">groups</i><span>องค์กรร่วม</span></a>');

		// if ($isAdmin) {
		// 	$dashboardUi->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.org.change').'" data-rel="box" data-width="480"><i class="icon -material">swap_horizontal_circle</i><span>ย้ายองค์กร</span></a>');
		// }

		// $ret .= $dashboardUi->build();

		$actionUi = new Ui(NULL, '-project-dashboard');
		$actionUi->addConfig('nav', '{class: "nav -app-menu"}');

		$actionUi->header('<h3>กิจกรรม</h3>');

		$actionUi->add('<a href="'.url('project/'.$projectId.'/info.action').'"><i class="icon -material">assignment</i><span>บันทึกกิจกรรม</span></a>');

		$actionUi->add('<a href="'.url('project/'.$projectId.'/info.adminreport').'"><i class="icon -material">assignment</i><span>บันทึกเจ้าหน้าที่</span></a>');

		if ($isEdit) {
			$actionUi->add('<a href="'.url('project/'.$projectId.'/info.register').'"><i class="icon -material">people</i><span>ใบลงทะเบียน</span></a>');
			$actionUi->add('<a href="'.url('project/'.$projectId.'/join').'"><i class="icon -material">monetization_on</i><span>ใบสำคัญรับเงิน</span></a>');
			if ($calid) {
				$actionUi->add('<a href="'.url('project/'.$projectId.'/info.join/'.$calid).'">บันทึกผู้เข้าร่วมกิจกรรม</a>');
			}
			$actionUi->add('<a href="'.url('project/'.$projectId.'/info.joins').'"><i class="icon -material">people</i><span>รายชื่อผู้เข้าร่วมกิจกรรม</span></a>');
		}

		$ret .= $actionUi->build();


		$reportUi = new Ui(NULL, '-project-dashboard');
		$reportUi->addConfig('nav', '{class: "nav -app-menu"}');

		$reportUi->header('<h3>รายงาน</h3>');

		$reportUi->add('<a href="'.url('project/'.$projectId.'/operate').'"><i class="icon -material">attach_money</i><span>การเงินประจำงวด</span></a>');
		$reportUi->add('<a href="'.url('project/'.$projectId.'/operate').'"><i class="icon -material">attach_money</i><span>สรุปการเงิน</span></a>');
		$reportUi->add('<a href="'.url('project/'.$projectId.'/operate').'"><i class="icon -material">pageview</i><span>ผลงานประจำงวด</span></a>');
		$reportUi->add('<a href="'.url('project/'.$projectId.'/info.summary').'"><i class="icon -material">pageview</i><span>รายงานสรุป</span></a>');
		$reportUi->add('<a href="'.url('project/'.$projectId.'/finalreport').'"><i class="icon -material">pageview</i><span>รายงานฉบับสมบูรณ์</span></a>');

		$ret .= $reportUi->build();

		$statusTextList = [
			'กำลังดำเนินโครงการ' => ['text' => 'โครงการกำลังอยู่ในระหว่างดำเนินงาน', 'icon' => 'directions_run'],
			'ดำเนินการเสร็จสิ้น' => ['text' => 'โครงการได้ดำเนินการเรียบร้อยแล้ว พร้อมทั้งส่งรายงานการเงินและผลการดำเนินงานเสร็จสมบูรณ์', 'icon' => 'verified'],
			'ยุติโครงการ' => ['text' => 'เจ้าของโครงการไม่สามารถดำเนินงานต่อไปได้ เจ้าของโครงการจึงขอหยุดการดำเนินงาน', 'icon' => 'pan_tool'],
			'ระงับโครงการ' => ['text' => 'โครงการมีปัญหาในการดำเนินงาน ผู้ให้ทุนโครงการจึงขอระงับไม่ให้ดำเนินงานต่อ', 'icon' => 'block'],
		];

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'sideBar' => R::View('project.dashboard.menu', $this->projectInfo),
			'body' => new Widget([
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'title' => 'ข้อมูลโครงการ',
								'leading' => '<i class="icon -material">info</i>',
							]), // ListTile
							new Nav([
								'mainAxisAlignment' => 'start',
								'class' => 'nav -app-menu -project-dashboard',
								'children' => [
									$isAdmin ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info.status').'" data-rel="box" data-width="480" data-tooltip="'.$statusTextList[$this->projectInfo->info->project_status]['text'].'"><i class="icon -material">'.$statusTextList[$this->projectInfo->info->project_status]['icon'].'</i><span>'.$this->projectInfo->info->project_status.'</span></a>' : NULL,
									'<a href="'.url('project/'.$projectId).'"><i class="icon -material">find_in_page</i><span>รายละเอียด</span></a>',
									'<a href="'.url('project/'.$projectId.'/info.calendar').'"><i class="icon -material">event</i><span>ปฏิทินกิจกรรม</span></a>',
									'<a class="sg-action" href="'.url('project/'.$projectId.'/info.share').'" data-rel="box" data-width="640" data-class-name="-transparent"><i class="icon -material">share</i><span>แบ่งปัน</span></a>',
									'<a href="'.url('project/'.$projectId.'/info.link').'"><i class="icon -material">link</i><span>เชื่อมโยง</span></a>',
									'<a class="sg-action" href="'.url('project/'.$projectId.'/info.org.co').'" data-rel="box" data-width="full"><i class="icon -material">groups</i><span>องค์กรร่วม</span></a>',
									$isAdmin ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info.org.change').'" data-rel="box" data-width="480"><i class="icon -material">swap_horizontal_circle</i><span>ย้ายองค์กร</span></a>' : NULL,
								],
							]), // Ui
						], // children
					]), // Card

					new Card([
						'children' => [
							new ListTile([
								'title' => 'กิจกรรม',
								'leading' => '<i class="icon -material">info</i>',
							]), // ListTile
							new Nav([
								'mainAxisAlignment' => 'start',
								'class' => 'nav -app-menu -project-dashboard',
								'children' => [
									'<a href="'.url('project/'.$projectId.'/info.action').'"><i class="icon -material">assignment</i><span>บันทึกกิจกรรม</span></a>',
									'<a href="'.url('project/'.$projectId.'/info.adminreport').'"><i class="icon -material">assignment</i><span>บันทึกเจ้าหน้าที่</span></a>',
									$isEdit ? '<a href="'.url('project/'.$projectId.'/info.register').'"><i class="icon -material">people</i><span>ใบลงทะเบียน</span></a>' : NULL,
									$isEdit ? '<a href="'.url('project/'.$projectId.'/join').'"><i class="icon -material">monetization_on</i><span>ใบสำคัญรับเงิน</span></a>' : NULL,
									$isEdit && $calid ? '<a href="'.url('project/'.$projectId.'/info.join/'.$calid).'">บันทึกผู้เข้าร่วมกิจกรรม</a>' : NULL,
									$isEdit ? '<a href="'.url('project/'.$projectId.'/info.joins').'"><i class="icon -material">people</i><span>รายชื่อผู้เข้าร่วมกิจกรรม</span></a>' : NULL,
								], // children
							]), // Nav
						], // children
					]), // Card


					new Card([
						'children' => [
							new ListTile([
								'title' => 'รายงาน',
								'leading' => '<i class="icon -material">info</i>',
							]), // ListTile
							new Nav([
								'mainAxisAlignment' => 'start',
								'class' => 'nav -app-menu -project-dashboard',
								'children' => [
									'<a href="'.url('project/'.$projectId.'/info.financials').'"><i class="icon -material">attach_money</i><span>การเงินประจำงวด</span></a>',
									'<a href="'.url('project/'.$projectId.'/info.progresses').'"><i class="icon -material">attach_money</i><span>ความก้าวหน้าประจำงวด</span></a>',
									'<a href="'.url('project/'.$projectId.'/operate.m2').'"><i class="icon -material">attach_money</i><span>สรุปการเงิน</span></a>',
									'<a href="'.url('project/'.$projectId.'/info.result').'"><i class="icon -material">pageview</i><span>รายงานสรุปผลการดำเนินงาน</span></a>',
									'<a href="'.url('project/'.$projectId.'/finalreport').'"><i class="icon -material">pageview</i><span>รายงานฉบับสมบูรณ์</span></a>',
								], // children
							]), // Nav
						], // children
					]), // Card

					$isAdmin ? new Card([
						'children' => [
							new ListTile([
								'title' => 'Admin',
								'leading' => '<i class="icon -material">admin_panel_settings</i>',
							]), // ListTile
							new Nav([
								'mainAxisAlignment' => 'start',
								'class' => 'nav -app-menu -project-dashboard',
								'children' => [
									'<a class="sg-action" href="'.url('project/'.$projectId.'/page.setting').'" data-rel="box" data-width="full"><i class="icon -material">settings</i><span>กำหนดค่า</span></a>',
									//'<a href="'.url('paper/'.$projectId.'/edit').'"><i class="icon -material">settings</i><span>จัดการหัวข้อ</span></a>',
									$isDeletable ? '<a class="sg-action" href="'.url('project/'.$projectId.'/info.delete').'" data-rel="box" data-width="640"><i class="icon -material">delete</i><span>ลบโครงการ</span></a>' : NULL,
								], // children
							]), // Nav
						], // children
					]) : NULL, // Card

				], // children
			]), // Widget
		]);
	}
}
?>