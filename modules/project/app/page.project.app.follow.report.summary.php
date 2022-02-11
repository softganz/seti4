<?php
/**
* Project :: View Follow Information
* Created 2021-01-27
* Modify  2021-01-27
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/app/follow/{id}/report.summary
*/

$debug = true;

function project_app_follow_report_summary($self, $projectInfo) {
	// Data model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$getMonth = SG\getFirst(post('m'), date('Y-m'));

	$isRight = $projectInfo->RIGHT & _IS_ACCESS;
	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;


	// View model
	$toolbar = new Toolbar($self, $projectInfo->title);
	$toolbarNav = new Ui();

	$ret = '';

	if ($projectInfo->info->prtype == 'โครงการ') {
		$ret .= '<section id="project-activity-card" class="sg-load" data-url="'.url('project/app/activity', array('id' => $projectId)).'">'._NL
			. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 32px auto; display: block;"></div>'._NL
			. '</section><!-- project-app-activity -->'._NL;
	} else {
		$toolbarNav->add('<a href="'.url('project/app/follow/'.$projectId, array('m' => $prevMonth)).'"><i class="icon -material">navigate_before</i><span>&nbsp</span></a>');
		$toolbarNav->add('<a href="'.url('project/app/follow/'.$projectId, array('m' => $getMonth)).'"><i class="icon -material">calendar_today</i><span>'.sg_date($getMonth.'-01','ดด ปปปป').'</span></a>');
		$toolbarNav->add('<a href="'.url('project/app/follow/'.$projectId, array('m' => $nextMonth)).'"><i class="icon -material">navigate_next</i><span>&nbsp</span></a>');

		$toolbarNav->add('<a class="sg-action" href="'.url('project/app/follow/'.$projectId.'/plan').'" data-webview="แผนงาน"><i class="icon -material">event</i><span>แผนกิจกรรม</span></a>');
		$toolbarNav->add('<a href="'.url('project/app/follow/'.$projectId.'/report.summary', array('m' => $getMonth)).'"><i class="icon -material">description</i><span>สรุปโครงการ</span></a>');
		$toolbar->addNav('main', $toolbarNav);


		// Show Report Menu
		$otherUi = new Ui();
		$otherUi->addConfig('nav', '{class: "nav -app-menu"}');
		$otherUi->header('<h3>รายงาน</h3>');

		//$otherUi->add('<a class="sg-action" href="'.url('project/app/follow/'.$projectId.'/report.send', array('m' => $getMonth)).'" data-webview="ใบตรวจงานประจำเดือน"><i class="icon -material">assessment</i><span>ใบตรวจงานประจำเดือน</span></a>');
		if ($isRight) {
			$otherUi->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.expense.month', array('m' => $getMonth)).'" data-webview="ค่าใช้จ่ายประจำเดือน"><i class="icon -material">assessment</i><span>ค่าใช้จ่ายประจำเดือน</span></a>');
		}

		$otherUi->add('<a class="sg-action" href="'.url('project/app/follow/'.$projectId.'/report.summary').'"><i class="icon -material">assessment</i><span>รายงานฉบับสมบูรณ์</span></a>');

		$ret .= $otherUi->build();

	}

	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>