<?php
function project_develop_plan_single($self, $tpid, $action = NULL) {
	$devInfo = R::Model('project.develop.get', $tpid);

	if (empty($devInfo)) return 'No project';

	$isEdit = ($devInfo->RIGHT & _IS_EDITABLE) && $action=='edit';

	$showBudget = $devInfo->is->showBudget;

	$objectiveNo = 0;
	$totalMainActBudget = 0;
	$totalBudget = $totalTarget = $totalActivity = $totalActivityBudget = 0;
	$j = 0;
	$actid = 0;
	$subBudget = $subTarget1 = $subTarget2 = $subActivity = $subActivityBudget = 0;


	foreach ($devInfo->activity as $activityInfo) {
		if (empty($activityInfo->trid)) continue;

		$ret .= R::View('project.develop.plan.render', $devInfo, $activityInfo, $isEdit);

		$subBudget += $activityInfo->budget;
		$subTarget1 += $activityInfo->targetChild+$activityInfo->targetTeen+$activityInfo->targetWork+$activityInfo->targetElder;
		$subTarget2 += $activityInfo->targetDisabled+$activityInfo->targetWoman+$activityInfo->targetMuslim+$activityInfo->targetWorker;
		$totalActivity += count($activityInfo->calendar[$activityInfo->trid]);

	}




	$totalBudget += $subBudget;

	if ($isEdit) {
		$addButtonText = 'เพิ่มกิจกรรมหลัก/แผนการดำเนินงาน';
		$addButtonText = 'เพิ่มกิจกรรม';
		$ret .= '<div id="project-develop-plan-add" class="project-develop-plan-add -no-print -sg-text-right">';
		$ret .= '<a class="sg-action btn -primary" data-rel="#project-develop-plan-add" href="'.url('project/develop/plan/'.$tpid.'/add',array('ret'=>'single')).'"><i class="icon -addbig -white"></i><span>'.$addButtonText.'</span></a>';
		$ret .= '</div>';
	}

	if ($showBudget) {
		$ret .= '<p>รวมงบประมาณทุกกิจกรรมของแผนการดำเนินงาน จำนวน <strong>'.number_format($totalBudget,2).'</strong> บาท</p>';



		$stmt = 'SELECT expCode.*, e.`trid`, `gallery`, SUM(e.`num4`) total
						FROM (
							SELECT DISTINCT eg.`catid` expGroup, eg.`name` expGroupName, ec.`catid` expc
							FROM %tag% eg
								RIGHT JOIN %tag% ec ON ec.`taggroup`="project:expcode" AND ec.`catparent`=eg.`catid`
							WHERE eg.`taggroup`="project:expgr"
						) expCode
						LEFT JOIN %project_tr% e ON e.`tpid`=:tpid AND e.`formid`="develop" AND e.`part`="exptr" AND e.`gallery`=expCode.`expc`
						WHERE e.`tpid`=:tpid GROUP BY expGroup';
		$dbs = mydb::select($stmt,':tpid',$tpid);

		$tables = new Table();
		$tables->thead[] = '';
		$row[] = 'ค่าใช้จ่าย (บาท)';
		$percent[] = 'เปอร์เซ็นต์ (%)';
		foreach ($dbs->items as $item) $total += $item->total;
		foreach ($dbs->items as $item) {
			$tables->thead['amt '.$item->expGroupName] = $item->expGroupName;
			$row[] = number_format($item->total,2);
			$percent[] = number_format($item->total*100/$total,2).'%';
		}
		$tables->thead['amt total'] = 'รวมเงิน';
		$row[] = '<strong>'.number_format($total,2).'</strong>';
		$percent[] = '<strong>100.00%</strong>';
		$tables->rows[] = $row;
		$tables->rows[] = $percent;
		$ret .= $tables->build();

		$ret .= '<a class="sg-action" href="'.url('project/develop/report/budgetbytype/'.$tpid).'" data-rel="box">ดูงบประมาณตามประเภท</a>';
		if ($isAdmin)
			$ret .= ' | <a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'calculateexp')).'" data-rel="#project-develop-plan">คำนวณค่าใช้จ่ายใหม่</a>';
	}

	//$ret.=print_o($devInfo,'$devInfo');

	return $ret;
}
?>