<?php
/**
* Project Calendar List By Objective
* Created 2019-08-08
* Modify  2019-09-05
*
* @param Object $self
* @param Int $projectInfo
* @return String
*/

$debug = true;

function project_info_plan_objective($self, $projectInfo) {
	$tpid = $projectInfo->tpid;
	$totalTarget = 0;

	$showBudget = $projectInfo->is->showBudget;

	$isEdit = $projectInfo->info->isEdit && post('mode') != 'view';

	setcookie('planby','objective',time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));


	$ret .= '<div id="project-plan-list" class="project-plan-list -objective" data-url="'.url('project/'.$tpid.'/info.plan.objective').'">';

	//$ret .= print_o($projectInfo,'$projectInfo');
	//$ret .= print_o($projectInfo->activity,'$activity');




	$objectiveNo = 0;
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
		''
	);

	foreach ($projectInfo->objective as $objId => $objective) {
		$subTarget=$subBudget=$subActivity=$subExpense=0;
		$objectiveNo++;

		$tables->rows[] = array('<td colspan="7"><h4>วัตถุประสงค์ข้อที่ '.$objectiveNo.' : '.$objective->title.'</h4></td>');


		foreach ($projectInfo->activity as $activityId => $activity) {

			$isEditCalendar = $isEdit && (empty($activity->fromdate) || $activity->fromdate > $projectInfo->info->lockReportDate);

			$ui = new Ui();
			$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.plan.view/'.$activity->activityId).'" data-rel="box" data-width="640" data-height="90%"><i class="icon -view"></i>รายละเอียดกิจกรรม</a>');
			if ($isEditCalendar) {
				$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit -showtext"></i><span>แก้ไขรายละเอียด</span></a>');
				$ui->add('<sep>');
				if ($activity->actionId) {
					$ui->add('<a href="javascript:void(0)" style="text-decoration:line-through;">ลบกิจกรรมไม่ได้</a>');
				} else {
					$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/activity.remove/'.$activity->activityId).'" data-rel="notify" data-done="load:#project-plan-list" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน?" data-title="ลบกิจกรรม"><i class="icon -material">delete</i><span>ลบกิจกรรม</span></a>');
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

			$class = 'calendar';
			if ($activity->childsCount) $class .= ' -has-child';

			$row = array(
				$isEditCalendar ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/'.$tpid.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>' : $actionDate,
				$isEditCalendar ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/'.$tpid.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" data-type="link" title="คลิกเพื่อแก้ไข">'.$activity->title.'</a>' : $activity->title,
				//view::inlineedit(array('group' => 'tr', 'fld' => 'detail1', 'tr' => $activity->trid, 'class' => '-fill', 'value' => $activity->title), $activity->title, $isEditCalendar),
				$activity->targetpreset,
				$showBudget ? ($activity->childsCount ? '<b style="color: #ccc">'.number_format($activity->planBudget,2).'</b>' : number_format($activity->budget, 2)) : '-',
				//$showBudget ? number_format($activity->budget, 2) : '-',
				$activity->actionId ? '<a class="sg-action" href="'.url('project/'.$tpid.'/action.view/'.$activity->actionId).'" data-rel="box" title="บันทึกหมายเลข '.$activity->actionId.'">✔</a>' : '',
				$showBudget && $activity->exp_total ? number_format($activity->exp_total, 2) : '-',
				$submenu,
				'config' => array('class' => $class)
			);

			if (in_array($objective->objectiveId, explode(',',$activity->objectiveId))) {
				$tables->rows[] = $row;
				unset($isNoObjective[$activity->trid]);
			} else {
				$isNoObjective[$activity->trid] = $row;
				continue;
			}


			$totalTarget += $activity->targetpreset;
	

			$subTarget+=$activity->targetpreset;
			$subBudget+=$activity->budget;
			if ($activity->activityId) $subActivity++;
			$subExpense+=$activity->exp_total;
		}

		$tables->rows[]=array(
			'',
			'รวม',
			number_format($subTarget),
			number_format($subBudget,2),
			number_format($subActivity),
			number_format($subExpense,2),
			'',
			'config'=>array('class'=>'subfooter')
		);

	}

	if ($isNoObjective) {
		$tables->rows[] = array('<td colspan="7"><h4>ไม่ระบุวัตถุประสงค์</h4></td>');
		foreach ($isNoObjective as $item) $tables->rows[] = $item;
	}

	$ret .= $tables->build();

	$ret .= '<p>หมายเหตุ : งบประมาณ และ ค่าใช้จ่าย รวมทุกวัตถุประสงค์อาจจะไม่เท่ากับงบประมาณรวมได้</p>';

	//$ret .= print_o($projectInfo->activity,'$activity');

	$ret .= '<!-- project-plan-list --></div>';

	return $ret;
}
?>
