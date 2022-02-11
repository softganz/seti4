<?php
/**
* Project :: Follow plan card
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/{id}/info.plan.card
*/

$debug = true;

function project_info_plan_card($self, $projectInfo) {
	// Data Model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->right->isEdit;


	// View Model
	$toolbar = new Toolbar($self, 'แผนงาน');
	$toolbarNav = new Ui();
	//$toolbarNav->add('<a href=""><i class="icon -material">add_circle</i><span>เพิ่มกลุ่มกิจกรรม</span></a>');
	$toolbar->addNav('main', $toolbarNav);

	$ret = '';

	$totalTarget = 0;

	$showBudget = $projectInfo->is->showBudget;

	//$isEdit = $projectInfo->info->isEdit && post('mode') != 'view';

	//setcookie('planby','tree',time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));


	$ret .= '<div id="project-plan-list" class="project-plan-list -tree" data-url="'.url('project/'.$projectId.'/info.plan.card').'">';

	$groupActivityCard = new Ui('div', 'ui-card -group-activity');
	$n0 = 0;
	foreach ($projectInfo->activity as $groupActivity) {
		//!childs OR parent
		if (!($groupActivity->childs || empty($groupActivity->parent))) continue;

		$cardUi = new Ui('div', 'ui-card -activity');
		$actNo = 0;
		foreach ($projectInfo->activity as $activityId => $activity) {
			if ($activity->parent != $groupActivity->activityId) continue;

			$activityCardNav = new Ui();
			$activityCardNav->addConfig('container', '{tag: "nav", class: "nav -header -sg-text-right"}');
			if ($isEdit) {
				$activityCardNav->add('<a class="sg-action btn -link" href="'.url('project/'.$projectId.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
				if ($activity->actionId) {
					$activityCardNav->add('<a class="btn -link" href="javascript:void(0)"><i class="icon -material -gray">delete</i></a>');
				} else {
					$activityCardNav->add('<a class="sg-action btn -link" href="'.url('project/'.$projectId.'/info/activity.remove/'.$activity->activityId).'" data-rel="notify" data-done="load:#project-plan-list" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน?" data-title="ลบกิจกรรม"><i class="icon -material">delete</i></a>');
				}
			}

			if (empty($activity->fromdate))
				$actionDate = '??/??/????';
			else if ($activity->fromdate==$activity->todate)
				$actionDate= sg_date($activity->fromdate,'ว ดด ปป');
			else if (sg_date($activity->fromdate, 'Y-m') == sg_date($activity->todate, 'Y-m'))
				$actionDate = sg_date($activity->fromdate, 'ว').' - '.sg_date($activity->todate, 'ว').' '.sg_date($activity->fromdate, 'ดด ปป');
			else
				$actionDate=sg_date($activity->fromdate, 'ว ดด ปป').($activity->todate ? ' - '.sg_date($activity->todate, 'ว ดด ปป') : '');

			$activityStr = '<div class="header"><h5><span class="-no">'.(++$actNo).'.</span> '.$activity->title.'</h5>'
				. $activityCardNav->build()
				. '</div>'
				. '<div class="detail">'
				. '@'.$actionDate
				. ($showBudget ? ' งบประมาณ '.number_format($activity->budget, 2).' บาท' : '')
				. ($activity->actionId ? ' <a class="sg-action" href="'.url('project/'.$projectId.'/action.view/'.$activity->actionId).'" data-rel="box" title="บันทึกหมายเลข '.$activity->actionId.'"><i class="icon -material -green">done_circle</i></a>' : '')
				. ($showBudget && $activity->totalExpense ? ' จ่ายแล้ว '.number_format($activity->totalExpense, 2).' บาท' : '')
				. '</div>';

			$cardUi->add($activityStr);
		}

		$groupActivityCardNav = new Ui();
		$groupActivityCardNav->addConfig('container', '{tag: "nav", class: "nav -header -sg-text-right"}');
		if ($isEdit) {
			$groupActivityCardNav->add('<a class="sg-action btn -link" href="'.url('project/app/month/kpi/'.$projectId).'" data-rel="box" data-width="640"><i class="icon -material">add_chart</i></a>');
			$groupActivityCardNav->add('<a class="sg-action btn -link" href="'.url('project/'.$projectId.'/info.plan.form/'.$groupActivity->activityId).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
			if ($groupActivity->actionId || $groupActivity->childs) {
				$groupActivityCardNav->add('<a class="btn -link" href="javascript:void(0)"><i class="icon -material -gray">delete</i></a>');
			} else {
				$groupActivityCardNav->add('<a class="sg-action btn -link" href="'.url('project/'.$projectId.'/info/activity.remove/'.$groupActivity->activityId).'" data-rel="notify" data-done="load:#project-plan-list" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน?" data-title="ลบกิจกรรม"><i class="icon -material">delete</i></a>');
			}

		}
		$cardStr = '<div class="header"><h3>'
			. '<span class="-no">'.(++$no).'.</span> '.$groupActivity->title.'</h3>'
			. $groupActivityCardNav->build()
			. '</div>'
			. '<div class="detail">'
			. 'งบประมาณ <b>'.number_format($groupActivity->planBudget,2).'</b> บาท';
		$cardStr .= $cardUi->build();
		$cardStr .= '</div>';

		if ($isEdit) {
			$nav = new Ui();
			$nav->addConfig('container', '{tag: "nav", class: "nav -card"}');
			$nav->add('<a class="sg-action btn -primary" href="'.url('project/'.$projectId.'/info.plan.form', array('parent' => $groupActivity->activityId)).'" data-rel="box" data-width="640"><i class="icon -material">add_circle</i><span>เพิ่มกิจกรรมย่อย</span></a>');
			$cardStr .= $nav->build();
		}
		$groupActivityCard->add($cardStr);
	}
	$ret .= $groupActivityCard->build();

	if ($isEdit) {
		$ret .= '<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/'.$projectId.'/info.plan.group.form').'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>เพิ่มกลุ่มกิจกรรม</span></a></nav>';
	}






	$groupNo = 0;
	$groupRows = array();
	$sumRows = array();
	$isNoObjective = false;

	$tables = new Table();
	$tables->addClass('-project-plan-list');
	$tables->thead = array(
		'date' => 'วันที่',
		'title' => 'ชื่อกิจกรรม',
		'target -amt -nowrap' => 'กลุ่มเป้าหมาย<br />(คน)',
		'budget -amt -nowrap' => 'งบกิจกรรม<br />(บาท)',
		'done -amt -nowrap' => 'ทำแล้ว<br />&nbsp;',
		'expend -amt -nowrap' => 'ใช้จ่ายแล้ว<br />(บาท)',
		'icons -c1' => ''
	);

	foreach ($projectInfo->activity as $activityMain) {
		if ($activityMain->childs) {
			$groupRows[$activityMain->activityId][] = array(
				'<td colspan="2"><b>'.(++$groupNo).'. '.$activityMain->title.'</b></td>',
				'',
				'',
				'',
				'',
				'',
				'config'=>array('class'=>'subfooter')
			);
		}
	}

	$groupRows['no'][] = array(
		'<td colspan="2"><b>ไม่มีการจัดกลุ่มกิจกรรม</b></td>',
		'',
		'',
		'',
		'',
		'',
		'config'=>array('class'=>'subfooter')
	);

	foreach ($projectInfo->activity as $activityId => $activity) {
		if ($activity->childs) continue;

		//$ret .= print_o($activity,'$activity');

		$groupId = $activity->parent ? $activity->parent : 'no';

		$isEditCalendar = $isEdit && (empty($activity->fromdate) || $activity->fromdate > $projectInfo->info->lockReportDate);

		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.plan.view/'.$activity->calid).'" data-rel="box" data-width="640" data-height="90%"><i class="icon -view"></i>รายละเอียดกิจกรรม</a>');
		if ($isEditCalendar) {
			$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit -showtext"></i><span>แก้ไขรายละเอียด</span></a>');
			$ui->add('<sep>');
			if ($activity->actionId) {
				$ui->add('<a href="javascript:void(0)" style="text-decoration:line-through;">ลบกิจกรรมไม่ได้</a>');
			} else {
				$ui->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info/activity.remove/'.$activity->activityId).'" data-rel="notify" data-done="load:#project-plan-list" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน?" data-title="ลบกิจกรรม"><i class="icon -material">delete</i><span>ลบกิจกรรม</span></a>');
			}
		}

		$submenu = '<nav class="nav -icons">'._NL;
		$submenu .= sg_dropbox($ui->build('ul'),'{type:"hover",class:"leftside submenu -no-print"}')._NL;
		$submenu .= '</nav>'._NL;




		if (empty($activity->fromdate))
			$actionDate = '??/??/????';
		else if ($activity->fromdate==$activity->todate)
			$actionDate= sg_date($activity->fromdate,'ว ดด ปป');
		else if (sg_date($activity->fromdate, 'Y-m') == sg_date($activity->todate, 'Y-m'))
			$actionDate = sg_date($activity->fromdate, 'ว').' - '.sg_date($activity->todate, 'ว').' '.sg_date($activity->fromdate, 'ดด ปป');
		else
			$actionDate=sg_date($activity->fromdate, 'ว ดด ปป').($activity->todate ? ' - '.sg_date($activity->todate, 'ว ดด ปป') : '');


		$row = array(
			$isEditCalendar ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/'.$projectId.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>' : $actionDate,
			$isEditCalendar ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/'.$projectId.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" data-type="link" title="คลิกเพื่อแก้ไข">'.$activity->title.'</a>' : $activity->title,
			//view::inlineedit(array('group' => 'tr', 'fld' => 'detail1', 'tr' => $activity->trid, 'class' => '-fill', 'value' => $activity->title), $activity->title, $isEditCalendar),
			number_format($activity->targetpreset),
			$showBudget ? number_format($activity->budget, 2) : '-',
			$activity->actionId ? '<a class="sg-action" href="'.url('project/'.$projectId.'/action.view/'.$activity->actionId).'" data-rel="box" title="บันทึกหมายเลข '.$activity->actionId.'">✔</a>' : '',
			$showBudget && $activity->totalExpense ? number_format($activity->totalExpense, 2) : '-',
			$submenu,
			'config' => array('class' => 'calendar')
		);

		$groupRows[$groupId][] = $row;

		$sumRows[$groupId]['target'] += $activity->targetpreset;
		$sumRows[$groupId]['budget'] += $activity->budget;
		$sumRows[$groupId]['action'] += $activity->actionId ? 1 : 0;
		$sumRows[$groupId]['totalExpense'] += $activity->totalExpense;

		$groupRows[$groupId][0][1] += $activity->targetpreset;
		$groupRows[$groupId][0][2] =  number_format(sg_strip_money($groupRows[$groupId][0][2]) + $activity->budget,2);
		$groupRows[$groupId][0][3] += $activity->actionId ? 1 : 0;
		$groupRows[$groupId][0][4] = number_format(sg_strip_money($groupRows[$groupId][0][4]) + $activity->totalExpense,2);

		$totalTarget += $activity->targetpreset;


		$subTarget += $activity->targetpreset;
		$subBudget += $activity->budget;
		if ($activity->activityId) $subActivity++;
		$subExpense += $activity->exp_total;
	}

	foreach ($groupRows as $groupId => $mainItem) {
		foreach ($mainItem as $item) {
			$tables->rows[] = $item;
			$totalAction += $item->actionId ? 1 : 0;
		}
	}
	//$ret .= $tables->build();

	//$ret .= print_o($sumRows,'$sumRows');
	//$ret .= print_o($groupRows,'$groupRows');

	if ($isEdit && $projectInfo->info->planBudget > 0 && $projectInfo->info->planBudget != $projectInfo->info->budget)
		$ret .= '<p class="notify">คำเตือน : งบประมาณรวมทุกกิจกรรรม ('.number_format($projectInfo->info->planBudget, 2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($projectInfo->info->budget, 2).' บาท)<br />กรุณาตรวจสอบความถูกต้องด้วยค่ะ!!!</p>';

	/*
	if ($isEdit || in_array($projectInfo->info->membershipType,array('FOLLOWER'))) {
		$ret .= '<nav class="nav -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$projectId.'/info.plan.form').'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>เพิ่มกิจกรรม</span></a></nav>';
	}
	*/

	//$ret .= print_o($projectInfo,'$projectInfo');
	//$ret .= print_o($projectInfo->activity,'$activity');

	$ret .= '<!-- project-plan-list --></div>';

	return $ret;
}
?>