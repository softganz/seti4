<?php
/**
* Project :: Action Card
* Created 2021-02-08
* Modify  2021-02-08
*
* @param Object $self
* @param Object $projectInfo
* @param Int $actionId
* @return String
*
* @usage project/{projectId}/info.action.card/{actionId}
*/

$debug = true;

function project_info_action_card($self, $projectInfo, $actionId = NULL) {
	// Data Model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	if ($actionId && is_numeric($actionId)) {
		$actionInfo = R::Model('project.action.get', ['projectId' => $projectId, 'actionId' => $actionId], '{debug: false}');
		$ret .= '<div class="ui-item -project-activity" id="project-action-'.$actionId.'" data-url="'.url('project/'.$projectId.'/info.action.card/'.$actionId).'">'
			. R::View(
				'project.action.card.render',
				$actionInfo,
				'{page: "'.(R()->appAgent ? 'app' : '').'"}'
			)
			. '</div>';
		//$ret .= print_o($actionInfo, '$actionInfo');
	}

	return $ret;







	$getStart = SG\getFirst(post('start'),0);
	$getUser = post('u');
	$getProjectId = post('id');
	$showItems = 10;
	$uid = i()->uid;
	$isAdmin = is_admin('project');

	$condition = new stdClass();
	$option = new stdClass();
	$option->start = $getStart;
	$option->item = $showItems;
	$option->actionOrder = '`trid` DESC';
	$option->order = 'ac.`trid` DESC';
	$option->debug = false;

	if ($getUser) $condition->userId = $getUser;
	if ($getProjectId) $condition->projectId = $getProjectId;

	$activity = R::Model('project.action.get', $condition, $option);
	$activityCount = count($activity);

	//$ret .= print_o($activity, '$activity');


	// View Model
	$ui = new Ui('div','ui-card project-activity');
	$ui->addId('project-activity');

	foreach ($activity as $rs) {
		$ui->add(
			R::View(
				'project.action.card.render',
				$rs,
				'{page: "'.(R()->appAgent ? 'app' : '').'"}'
			),
			'{class: "-proejct-activity", id: "project-action-'.$rs->actionId.'", "data-url": "'.url('project/'.$rs->projectId.'/info.action.card/'.$rs->actionId).'"}'
		);
	}

	if ($start == 0 && empty($activityCount)) {
		$ui->add('<p class="-sg-text-center" style="padding: 32px 0;">ยังไม่มีกิจกรรม</p>');
	}

	$ret .= $ui->build().'<!-- project-activity -->';


	if ($activityCount && $activityCount == $showItems) {
		$ret .= '<div id="more" class="green-activity-more" style="padding: 24px 16px 44px;">'
			. '<a class="sg-action btn -primary" href="'.url('project/app/activity',array('u' => $getUser, 'start' => $getStart+$activityCount)).'" data-rel="replace:#more" style="margin:0 auto;display:block;text-align:center; padding: 16px 0;">'
			. '<span>{tr:More}</span>'
			. '<i class="icon -material">chevron_right</i>'
			. '</a>'
			. '</div>';
	}

	return $ret;
}
?>