<?php
/**
* Project :: Activity
* Created 2021-01-20
* Modify  2021-01-20
*
* @param Object $self
* @param Int $start
* @return String
*
* @usage project/app/activity
*/

$debug = true;

function project_app_activity($self) {
	// Data Model
	$getStart = SG\getFirst(post('start'),0);
	$getUser = post('u');
	$getProjectId = post('id');
	$showItems = 10;
	$uid = i()->uid;
	$isAdmin = is_admin('project');

	$conditions = new stdClass();
	$option = new stdClass();
	$option->start = $getStart;
	$option->item = $showItems;
	$option->actionOrder = '`trid` DESC';
	$option->order = 'ac.`trid` DESC';
	$option->debug = false;

	if ($getUser) $conditions->uid = $getUser;
	if ($getProjectId) $conditions->tpid = $getProjectId;

	$activity = R::Model('project.action.get2', $conditions, $option);
	$activityCount = count($activity);

	//$ret .= print_o($activity, '$activity');
	if ($getUser && empty($getStart)) {
		$userInfo = R::Model('user.get', $getUser);
	} else if ($getProjectId && empty($getStart)) {
		$projectInfo = R::Model('project.get', $getProjectId, '{data: "info"}');
		$userInfo = R::Model('user.get', $projectInfo->uid);
	}

	// View Model
	if ($userInfo && empty($getStart)) {
		$ret .= '<div class="my-profile-wrapper">';

		$ret .= '<div class="-photo"><img src="'.model::user_photo($userInfo->username).'" width="100%" height="100%" /></a></div>';
		$ret .= '<div class="-name">'.$userInfo->name.'</div>';
		$ret .= '</div>';
	}

	$ui = new Ui('div','ui-card project-activity');
	$ui->addId('project-activity');

	foreach ($activity as $rs) {
		$ui->add(
			R::View(
				'project.action.card.render',
				$rs,
				'{page: "'.(R()->appAgent ? 'app' : '').'"}'
			),
			array(
				'class' => '-project-activity',
				'id' => 'project-action-'.$rs->actionId,
				'data-url' => url('project/'.$rs->projectId.'/info.action.card/'.$rs->actionId),
			)
		);
	}

	if ($start == 0 && empty($activityCount)) {
		$ui->add('<p class="-sg-text-center" style="padding: 32px 0;">ยังไม่มีกิจกรรม</p>');
	}

	$ret .= $ui->build().'<!-- project-activity -->';


	if ($activityCount && $activityCount == $showItems) {
		$ret .= '<div id="more" class="green-activity-more" style="padding: 24px 16px 44px;">'
			. '<a class="sg-action btn -primary" href="'.url('project/app/activity',array('u' => $getUser, 'id' => $getProjectId, 'start' => $getStart+$activityCount)).'" data-rel="replace:#more" style="margin:0 auto;display:block;text-align:center; padding: 16px 0;">'
			. '<span>{tr:More}</span>'
			. '<i class="icon -material">chevron_right</i>'
			. '</a>'
			. '</div>';
	}

	return $ret;
}
?>