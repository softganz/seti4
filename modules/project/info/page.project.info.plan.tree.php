<?php
/**
* Project Activity List By Tree
* Created 2019-08-08
* Modify  2022-02-04
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.plan.tree
*/

class ProjectInfoPlanTree extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$isEditMode = $this->projectInfo->info->isEdit && post('mode') != 'view';
		$this->right = (Object) [
			'edit' => $isEditMode,
			'addActivity' => $isEditMode || in_array($projectInfo->info->membershipType, ['FOLLOWER']),
		];
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectInfo = $this->projectInfo;
		$totalTarget = 0;

		$showBudget = $projectInfo->is->showBudget;

		setcookie('planby','tree',time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));



		$groupNo = 0;
		$groupRows = [];
		$sumRows = [];
		$isNoObjective = false;

		$tables = new Table([
			'class' => '-project-plan-list',
			'showHeader' => false,
			'thead' => [
				'date' => 'วันที่',
				'title' => 'ชื่อกิจกรรม',
				'target -amt -nowrap' => 'กลุ่มเป้าหมาย<br />(คน)',
				'budget -amt -nowrap' => 'งบกิจกรรม<br />(บาท)',
				'done -amt -nowrap' => 'ทำแล้ว<br />&nbsp;',
				'expend -amt -nowrap' => 'ใช้จ่ายแล้ว<br />(บาท)',
				'icons -c1' => ''
			],
		]);

		foreach ($projectInfo->activity as $activityMain) {
			if ($activityMain->tagName == 'group' || $activityMain->childs) {
				$groupRows[$activityMain->activityId]['title'] = [
					'<td colspan="2">'
					. ($this->right->edit ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/'.$this->projectId.'/info.plan.group.form/'.$activityMain->activityId).'" data-rel="box" data-width="640" data-type="link" title="คลิกเพื่อแก้ไข">'.++$groupNo.'. '.$activityMain->title.'</a>' : ++$groupNo.'. '.$activityMain->title)
					. '</td>',
					'target -amt -nowrap' => 'กลุ่มเป้าหมาย<br />(คน)',
					'budget -amt -nowrap' => 'งบกิจกรรม<br />(บาท)',
					'done -amt -nowrap' => 'ทำแล้ว<br />&nbsp;',
					'expend -amt -nowrap' => 'ใช้จ่ายแล้ว<br />(บาท)',
					'icons -c1' => '',
					'config' => ['class' => 'header-group']
				];
				$groupRows[$activityMain->activityId]['header'] = [
					'วันที่',
					'กิจกรรมย่อย',
					0,
					0,
					0,
					0,
					'',
					'config' => ['class' => 'subfooter']
				];
			}
		}

			$groupRows['no']['title'] = [
				'<td colspan="2"><i class="icon -material">hourglass_empty</i><span>ไม่มีกลุ่มกิจกรรม</span></td>',
				'target -amt -nowrap' => 'กลุ่มเป้าหมาย<br />(คน)',
				'budget -amt -nowrap' => 'งบกิจกรรม<br />(บาท)',
				'done -amt -nowrap' => 'ทำแล้ว<br />&nbsp;',
				'expend -amt -nowrap' => 'ใช้จ่ายแล้ว<br />(บาท)',
				'icons -c1' => '',
				'config' => ['class' => 'header-group']
			];
		$groupRows['no']['header'] = [
			'วันที่',
			'กิจกรรม',
			0,
			0,
			0,
			0,
			'',
			'config' => ['class' => 'subfooter']
		];

		foreach ($projectInfo->activity as $activityId => $activity) {
			if ($activity->tagName == 'group' || $activity->childs) continue;

			//$ret .= print_o($activity,'$activity');

			$groupId = $activity->parent ? $activity->parent : 'no';

			$isEditCalendar = $this->right->edit && (empty($activity->fromdate) || $activity->fromdate > $projectInfo->info->lockReportDate);

			$ui = new Ui();
			$ui->add('<a class="sg-action" href="'.url('project/'.$this->projectId.'/info.plan.view/'.$activity->calid).'" data-rel="box" data-width="640" data-height="90%"><i class="icon -view"></i>รายละเอียดกิจกรรม</a>');
			if ($isEditCalendar) {
				$ui->add('<a class="sg-action" href="'.url('project/'.$this->projectId.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" title="แก้ไขรายละเอียดกิจกรรมย่อย"><i class="icon -edit -showtext"></i><span>แก้ไขรายละเอียด</span></a>');
				$ui->add('<sep>');
				if ($activity->actionId) {
					$ui->add('<a href="javascript:void(0)" style="text-decoration:line-through;">ลบกิจกรรมไม่ได้</a>');
				} else {
					$ui->add('<a class="sg-action" href="'.url('project/'.$this->projectId.'/info/activity.remove/'.$activity->activityId).'" data-rel="notify" data-done="load:#project-plan-list" data-confirm="ลบกิจกรรมย่อย กรุณายืนยัน?" data-title="ลบกิจกรรม"><i class="icon -material">delete</i><span>ลบกิจกรรม</span></a>');
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


			$row = [
				$isEditCalendar ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/'.$this->projectId.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" data-type="link" title="คลิกเพื่อแก้ไข">'.$actionDate.'</a>' : $actionDate,
				$isEditCalendar ? '<a class="sg-action inline-edit-field -fill" href="'.url('project/'.$this->projectId.'/info.plan.form/'.$activity->activityId).'" data-rel="box" data-width="640" data-type="link" title="คลิกเพื่อแก้ไข">'.$activity->title.'</a>' : $activity->title,
				//view::inlineedit(array('group' => 'tr', 'fld' => 'detail1', 'tr' => $activity->trid, 'class' => '-fill', 'value' => $activity->title), $activity->title, $isEditCalendar),
				number_format($activity->targetpreset),
				$showBudget ? number_format($activity->budget, 2) : '-',
				$activity->actionId ? '<a class="sg-action" href="'.url('project/'.$this->projectId.'/action.view/'.$activity->actionId).'" data-rel="box" title="บันทึกหมายเลข '.$activity->actionId.'">✔</a>' : '',
				$showBudget && $activity->totalExpense ? number_format($activity->totalExpense, 2) : '-',
				$submenu,
				'config' => ['class' => 'calendar']
			];

			$groupRows[$groupId][] = $row;
			// debugMsg($groupRows,'$groupRows');
			$sumRows[$groupId]['target'] += $activity->targetpreset;
			$sumRows[$groupId]['budget'] += $activity->budget;
			$sumRows[$groupId]['action'] += $activity->actionId ? 1 : 0;
			$sumRows[$groupId]['totalExpense'] += $activity->totalExpense;

			// if (!array_key_exists(0, array_keys($groupRows[$groupId]))) $groupRows[$groupId][0] = [];
			// echo '$groupId = '.$groupId;
			$groupRows[$groupId]['header'][2] += intval($activity->targetpreset);
			$groupRows[$groupId]['header'][3] =  number_format(sg_strip_money($groupRows[$groupId]['header'][3]) + $activity->budget,2);
			$groupRows[$groupId]['header'][4] = intval($groupRows[$groupId]['header'][4]) + ($activity->actionId ? 1 : 0);
			$groupRows[$groupId]['header'][5] = number_format(sg_strip_money($groupRows[$groupId]['header'][5]) + $activity->totalExpense,2);

			$totalTarget += $activity->targetpreset;


			$subTarget += $activity->targetpreset;
			$subBudget += $activity->budget;
			if ($activity->activityId) $subActivity++;
			$subExpense += $activity->exp_total;
		}

		foreach ($groupRows as $groupId => $mainItem) {
			if ($groupId == 'no' && count($mainItem) <= 2) continue;

			/*
			$tables->children[] = array(
				'<td></td>',
				'<td>รวม</td>',
				number_format($sumRows[$groupId]['target']),
				number_format($sumRows[$groupId]['budget'],2),
				number_format($sumRows[$groupId]['action']),
				number_format($sumRows[$groupId]['totalExpense'],2),
				'',
				'config'=>array('class'=>'subfooter')
			);
			*/
			/*
			$groupItem = $mainItem[0];
			$groupItem[1] = number_format($groupItem[1]);
			$groupItem[2] = number_format($groupItem[2],2);
			$groupItem[3] = number_format($groupItem[3]);
			$groupItem[4] = number_format($groupItem[4],2);

			$mainItem[0] = $groupItem;
			*/

			foreach ($mainItem as $item) {
				$tables->children[] = $item;
				$totalAction += $item->actionId ? 1 : 0;
			}
			if ($groupId != 'no') {
				$tables->children[] = ['<td colspan="7" class="-sg-text-right">'.($this->right->addActivity ? '<nav class="nav -sg-text-right -sg-paddingnorm"><a class="sg-action btn -primary" href="'.url('project/'.$this->projectId.'/info.plan.form', ['parent' => $groupId]).'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่มกิจกรรมย่อย</span></a></nav>' : NULL).'</td>'];
			}
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]), // AppBar
			'body' => new Container([
				'id' => 'project-plan-list',
				'class' => 'project-plan-list -tree',
				'attribute' => [
					'data-url' => url('project/'.$this->projectId.'/info.plan.tree'),
				],
				'children' => [
					$tables,

					$this->right->addActivity ? '<nav class="nav -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/'.$this->projectId.'/info.plan.group.form').'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>สร้างกลุ่มกิจกรรม</span></a></nav>' : NULL,

					$this->right->edit && $projectInfo->info->planBudget > 0 && $projectInfo->info->planBudget != $projectInfo->info->budget ? '<p class="notify">คำเตือน : งบประมาณรวมทุกกิจกรรรม ('.number_format($projectInfo->info->planBudget, 2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($projectInfo->info->budget, 2).' บาท)<br />กรุณาตรวจสอบความถูกต้องด้วยค่ะ!!!</p>' : NULL,

					// new DebugMsg($groupRows, '$groupRows'),
					// new DebugMsg($this->projectInfo, '$this->projectInfo'),
				], // children
			]), // Container
		]);
	}
}
?>