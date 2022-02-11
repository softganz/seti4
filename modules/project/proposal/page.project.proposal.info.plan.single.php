<?php
/**
* Project Proposal Plan Single
* Created 2019-09-22
* Modify  2019-09-22
*
* @param Object $self
* @param Object $proposalInfo
* @return String
*/

$debug = true;

function project_proposal_info_plan_single($self, $proposalInfo, $action = NULL) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $proposalInfo->RIGHT & _IS_EDITABLE;
	$isEdit = $isEditable && $action == 'edit';

	$showBudget = $proposalInfo->is->showBudget;

	$objectiveNo = 0;
	$totalMainActBudget = 0;
	$totalBudget = $totalTarget = $totalActivity = $totalActivityBudget = 0;
	$j = 0;
	$actid = 0;
	$subBudget = $subTarget1 = $subTarget2 = $subActivity = $subActivityBudget = 0;

	$ret .= '<div id="project-proposal-plan" class="project-proposal-plan -single" data-url="'.url('project/proposal/'.$tpid.'/info.plan.single/'.$action).'">';

	$cardUi = new Ui('div', 'ui-card');

	foreach ($proposalInfo->activity as $activityInfo) {
		if (empty($activityInfo->trid)) continue;

		$cardUi->add(
			R::View('project.proposal.plan.render', $proposalInfo, $activityInfo, $isEdit),
			array(
				'id' => 'plan-detail-'.$activityInfo->trid,
				'class' => 'project-develop-plan-item -sg-flex',
			)
		);

		$subBudget += $activityInfo->budget;
		$subTarget1 += $activityInfo->targetChild+$activityInfo->targetTeen+$activityInfo->targetWork+$activityInfo->targetElder;
		$subTarget2 += $activityInfo->targetDisabled+$activityInfo->targetWoman+$activityInfo->targetMuslim+$activityInfo->targetWorker;
		$totalActivity += count($activityInfo->calendar[$activityInfo->trid]);

	}

	$ret .= $cardUi->build();



	$totalBudget += $subBudget;

	if ($isEdit) {
		$addButtonText = 'เพิ่มกิจกรรมหลัก/แผนการดำเนินงาน';
		$addButtonText = 'เพิ่มกิจกรรม';
		$ret .= '<div id="project-develop-plan-add" class="project-develop-plan-add -no-print -sg-text-right">';
		$ret .= '<a class="sg-action btn -primary" href="'.url('project/proposal/'.$tpid.'/info.plan.form',array('ret'=>'single')).'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>'.$addButtonText.'</span></a>';
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

		$ret .= '<nav class="nav -page -sg-text-right -no-print"><a class="sg-action btn -link" href="'.url('project/proposal/'.$tpid.'/info.budgetbytype').'" data-rel="box">ดูงบประมาณตามประเภท</a>';
		if ($isAdmin)
			$ret .= ' <a class="sg-action btn -link" href="'.url('project/proposal/'.$tpid.'/info/exp.calculate').'" data-rel="notify" data-done="load->replace:#project-develop-plan">คำนวณค่าใช้จ่ายใหม่</a>';
		$ret .= '</nav>';
	}

	$ret .= '<!-- project-proposal-plan -->';
	$ret .= '</div>';

	//$ret.=print_o($proposalInfo,'$proposalInfo');

	return $ret;
}
?>