<?php
/**
* Project :: View Follow Information
* Created 2021-01-27
* Modify  2021-01-27
*
* @param Object $self
* @param Object $projectInfo
* @param Int $periodId
* @return String
*
* @usage project/app/follow/{id}/report.send
*/

$debug = true;

function project_app_follow_report_send($self, $projectInfo, $periodId = 1) {
	// Data model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$getReportType = post('type');
	$getMonth = SG\getFirst(post('m'), date('Y-m'));

	$childId = array();
	$cfgFollow = cfg('project')->follow;

	if ($projectInfo->info->ownertype == 'tambon') {

		$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

		$childList = R::Model('project.follows', '{childOf: '.$projectId.', status: "process"}', '{order: "CONVERT(t.`title` USING tis620)", sort: "ASC", debug: false}');

		foreach ($childList->items as $rs) {
			if (in_array($rs->ownertype, array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT,_PROJECT_OWNERTYPE_PEOPLE))) {
				$childId[] = $rs->tpid;
			}
		}

		if ($childId) {
			$projectIdList = 'SET:'.implode(',',$childId);

			$stmt = 'SELECT `period`, `date1` `dateStart`, `date2` `dateEnd`
				FROM %project_tr% p
				WHERE `tpid` IN (:projectIdList) AND `formid` = "info" AND `part` = "period"
				GROUP BY `period`
				ORDER BY `period` ASC;
				-- {key: "period"}';
			$periodList = mydb::select($stmt, ':projectIdList', $projectIdList)->items;

			$getMonth = $periodList[$periodId]->dateEnd;

			$actionPara = new stdClass();
			$actionOptions = new stdClass();
			$actionPara->projectId = implode(',',$childId);
			$actionPara->dateFrom = $periodList[$periodId]->dateStart;
			$actionPara->dateEnd = $periodList[$periodId]->dateEnd;
			$actionOptions->debug = false;
			$actionOptions->includePhoto = false;
			//$actionOptions->order = 'CONVERT(`projectTitle` USING tis620) ASC';

			$actionList = R::Model('project.action.get', $actionPara, $actionOptions);
			//debugMsg($periodList, '$periodList');
			//debugMsg($actionList, '$actionList');

			$stmt = 'SELECT
				-- a.`tpid`, a.`trid`, a.`date1`, pd.`period`, pd.`date1` `dateStart`, pd.`date2` `dateEnd`
				pd.`tpid`, pd.`num2` `paidAmt`, COUNT(a.`trid`) `actions`, COUNT(DISTINCT a.`date1`) `days`, pd.`flag`
				FROM %project_tr% pd
					LEFT JOIN %project_tr% a ON a.`tpid` = pd.`tpid` AND a.`formid` = "activity" AND a.`part` = "owner" AND a.`date1` BETWEEN pd.`date1` AND pd.`date2`
				WHERE pd.`tpid` IN (:projectIdList) AND pd.`formid` = "info" AND pd.`part` = "period" AND pd.`period` = :period
				GROUP BY `tpid`;
				-- {key: "tpid"}';
			$actionCount = mydb::select($stmt, ':projectIdList', 'SET:'.implode(',', $childId), ':period', $periodId)->items;
			//debugMsg(mydb()->_query);
			//debugMsg($actionCount, '$actionCount');

		}
	}



	// View model
	$ret = '<section class="project-follow-report-send">';

	$ret .= '<header class="header"><h3>ใบตรวจงานประจำเดือน '.sg_date($getMonth.'-01', 'ดดด ปปปป').'</h3>'
		. '<nav class="nav -no-print">'
		. ($getReportType == 'short' ? '<a class="sg-action btn -primary" href="'.url('project/app/follow/'.$projectId.'/report.send/'.$periodId).'" data-rel="#main"><i class="icon -material">view_list</i><span>แบบย่อ</span></a>' : '<a class="sg-action btn -primary" href="'.url('project/app/follow/'.$projectId.'/report.send/'.$periodId,array('type' => 'short')).'" data-rel="#main"><i class="icon -material">view_stream</i><span>แบบละเอียด</span></a>')
		. '</nav>'
		. '</header>';

	$no = 0;
	$tables = new Table();
	$tables->thead = array(
		'no' => '',
		'ชื่อ - นามสกุล',
		'type -center' => 'ประเภท',
		'days -amt' => 'วัน/กิจกรรม',
		'money -money' => 'เบิกเงิน',
		'sign -center' => 'รับรอง',
	);
	foreach ($childList->items as $rs) {
		$tables->rows[] = array(
			++$no,
			$rs->title,
			$cfgFollow->ownerType->{$rs->ownertype}->title,
			$actionCount[$rs->tpid]->actions ? $actionCount[$rs->tpid]->days.'/'.$actionCount[$rs->tpid]->actions : '-',
			$actionCount[$rs->tpid]->paidAmt ? number_format($actionCount[$rs->tpid]->paidAmt, 2) : '-',
			'.......,.......,.......',
		);
	}
	$ret .= $tables->build();
	$ret .= '<hr class="pagebreak" />';

	$ret .= '<h3>รายละเอียดกิจกรรม</h3>';

	$detailTable = new Table();
	$detailTable->thead = array('actiondate -date' => 'วันที่', 'กิจกรรม');
	$detailTable->addConfig('showHeader', false);
	$currentUser = NULL;
	foreach ($childId as $childProjectId) {
		$cardUi = new Ui(NULL, 'ui-card');
		foreach ($actionList as $activity) {
			if ($activity->tpid != $childProjectId) continue;
			$projectTitle = $activity->projectTitle;

			if ($getReportType == 'short') {
				if ($currentUser != $activity->projectId) {
					$currentUser = $activity->projectId;
					$detailTable->rows[] = array('<td colspan="2" class="-sg-text-left">'.$activity->projectTitle.'</td>','config' => '{class: "subheader"}');
				}

				$detailTable->rows[] = array(
					sg_date($activity->actionDate, 'ว ดด ปปปป'),
					$activity->title,
				);
			} else {
				$cardStr = '<div class="header">'
					. '<h5>'
					. 'วันที่ '.sg_date($activity->actionDate, 'ว ดดด ปปปป').' '
					. $activity->title
					.'</h5>'
					. '</div>'
					. '<div class="detail">'
					//. '<h5>รายละเอียด</h5>'
					. nl2br($activity->actionReal)
					. ($activity->outputOutcomeReal ? '<h5>ผลผลิต/ผลลัพท์</h5>'.nl2br($activity->outputOutcomeReal) : '')
					. '</div>'
					//. print_o($activity, '$activity')
					;
				$cardUi->add($cardStr);
			}
			//$ret .= print_o($activity, '$activity');
		}
		if ($cardUi->count()) {
			$ret .= '<h4>'.$projectTitle.'</h4>'
				. $cardUi->build();
		}
	}

	if ($getReportType == 'short') {
		$ret .= $detailTable->build();
	}

	$ret .= '<style type="text/css">
	.project-follow-report-send>h3 {margin: 16px 0; background-color: #fff; padding: 8px;}
	.project-follow-report-send>div>h3 {}
	.project-follow-report-send>h4 {margin: 0 0 4px 0; background-color: #fff; font-size: 1.2em; padding: 8px;}
	</style>';
	$ret .= '</section>';

	//$ret .= print_o($childId, '$childId');
	//$ret .= print_o($actionList, '$actionList');
	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>