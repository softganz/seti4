<?php
/**
* Project Calendar List By Time
* Created 2019-08-08
* Modify  2019-09-05
*
* @param Object $self
* @param Int $projectInfo
* @return String
*/

$debug = true;

function project_info_plan_time($self, $projectInfo) {
	$tpid = $projectInfo->tpid;

	// R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo,'{showPrint: fasle}');

	$lockReportDate = project_model::get_lock_report_date($tpid);
	$totalTarget = 0;

	$showBudget = $projectInfo->is->showBudget;

	$isEdit = $projectInfo->info->isEdit && post('mode') != 'view';

	setcookie('planby',NULL,time()-10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	//$activityList = R::Model('project.activity.get.bytpid', $tpid);
	//$ret .= $lockReportDate;

	$ret .= '<div id="project-plan-list" class="project-plan-list -time" data-url="'.url('project/'.$tpid.'/info.plan.time').'">';

	$tables = new Table();
	$tables->addClass('-project-plan-list');
	$tables->thead = array(
		'date no' => 'วันที่',
		'title' => 'ชื่อกิจกรรม',
		'target -amt -nowrap' => 'กลุ่มเป้าหมาย<br />(คน)',
		'budget -amt -nowrap' => 'งบกิจกรรม<br />(บาท)',
		'done -amt -nowrap' => 'ทำแล้ว<br />&nbsp;',
		'expend -amt -nowrap' => 'ใช้จ่ายแล้ว<br />(บาท)',
		'icons -c1' => ''
	);

	foreach ($projectInfo->activity as $activity) {
		$isSubCalendar = true;
		$isEditCalendar = ($isEdit || (i()->ok && $activity->uid == i()->uid)) && (empty($activity->fromdate) || $activity->fromdate > $lockReportDate);

		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.plan.view/'.$activity->activityId).'" data-rel="box" data-width="640" data-height="90%"><i class="icon -view"></i>รายละเอียดกิจกรรม</a>');
		if ($isEditCalendar) {
			if ($isEditCalendar) $ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit -showtext"></i><span>แก้ไขรายละเอียด</span></a>');
			$ui->add('<sep>');
			if ($isEditCalendar) {
				if ($activity->actionId) {
					$ui->add('<a href="javascript:void(0)" style="text-decoration:line-through;">ลบกิจกรรมไม่ได้</a>');
				} else {
					$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/activity.remove/'.$activity->activityId).'" data-rel="notify" data-done="load:#project-plan-list" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน?" data-title="ลบกิจกรรม"><i class="icon -material">delete</i><span>ลบกิจกรรม</span></a>');
				}
			}
		}

		$submenu='<nav class="nav -icons">'._NL;
		$submenu.=sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
		$submenu.='</nav>'._NL;




		if (empty($activity->fromdate))
			$actionDate = '??/??/????';
		else if ($activity->fromdate==$activity->todate)
			$actionDate= sg_date($activity->fromdate,'ว ดด ปป');
		else if (sg_date($activity->fromdate, 'Y-m') == sg_date($activity->todate, 'Y-m'))
			$actionDate = sg_date($activity->fromdate, 'ว').' - '.sg_date($activity->todate, 'ว').' '.sg_date($activity->fromdate, 'ดด ปป');
		else
			$actionDate=sg_date($activity->fromdate, 'ว ดด ปป').($activity->todate ? ' - '.sg_date($activity->todate, 'ว ดด ปป') : '');


		$class = 'calendar';
		if ($activity->childsCount) $class .= ' -has-child';

		$tables->rows[] = array(
			$isEditCalendar ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/'.$tpid.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>' : $actionDate,
			$isEditCalendar ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/'.$tpid.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" data-type="link" title="คลิกเพื่อแก้ไข">'.$activity->title.'</a>' : $activity->title,
			//view::inlineedit(array('group' => 'calendar', 'fld' => 'title', 'tr' => $activity->id, 'class' => '-fill', 'value' => $activity->title), $activity->title, $isEditCalendar),
			$activity->targetpreset,
			$showBudget ? ($activity->childsCount ? '<b style="color: #ccc">'.number_format($activity->planBudget,2).'</b>' : number_format($activity->budget, 2)) : '-',
			$activity->actionId ? '<a class="sg-action" href="'.url('project/'.$tpid.'/action.view/'.$activity->actionId).'" title="บันทึกหมายเลข '.$activity->actionId.'" data-rel="box">✔</a>' : '',
			$showBudget && $activity->totalExpense ? number_format($activity->totalExpense, 2) : '-',
			$submenu,
			'config' => array('class' => $class)
		);


		if (empty($activity->childsCount)) {
			$totalTarget += $activity->targetpreset;
			$totalBudget += $activity->budget;
			$totalExpense += $activity->totalExpense;
			$totalAction += $activity->actionId ? 1 : 0;
		}
	}



	// Show total
	$tables->rows[] = array(
		'',
		'รวม',
		number_format($totalTarget),
		$showBudget ? number_format($totalBudget, 2) : '-',
		number_format($totalAction),
		$showBudget ? number_format($totalExpense, 2) : '-',
		'', //$isEdit?'<a class="sg-action" href="'.url('project/calendar/'.$tpid.'/add').'" data-rel="box" title="เพิ่มกิจกรรม"><i class="icon -add -hidetext"></i><span class="-hidden">เพิ่มกิจกรรม</span></a>':'',
		'config'=>array('class' => 'subfooter')
	);

	$ret .= $tables->build();


	if ($isEdit || in_array($projectInfo->info->membershipType,array('FOLLOWER'))) {
		$ret .= '<nav class="nav -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$tpid.'/info.plan.form').'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>เพิ่มกิจกรรม</span></a></nav>';
	}

	if ($isEdit && $totalBudget > 0 && $totalBudget != $projectInfo->info->budget)
		$ret .= '<p class="notify">คำเตือน : งบประมาณรวมทุกกิจกรรรม ('.number_format($totalBudget, 2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($projectInfo->info->budget, 2).' บาท)<br />กรุณาตรวจสอบความถูกต้องด้วยค่ะ!!!</p>';
	/*
	if ($info->summary->totalBudget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมย่อย ('.number_format($info->summary->totalBudget,2).' บาท)</p>';

	if ($info->project->budget!=$info->summary->budget) $ret.='<p class="notify">คำเตือน : งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท) ไม่เท่ากับ งบประมาณของกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท)</p>';
	*/

	//$ret.=print_o($activity,'$activity');
	//$ret .= print_o($projectInfo,'$projectInfo');

	$ret .= '<!-- project-plan-list --></div>';

	return $ret;
}
?>
