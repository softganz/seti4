<?php
/**
* Project :: App Activity
* Created 2021-01-20
* Modify  2021-09-08
*
* @return Widget
*
* @usage project/app/activity
*/

$debug = true;

import('widget:project.actions');

class ProjectAppActivity extends Page {
	var $start;
	var $userId;
	var $projectId;

	function __construct() {
		$this->start = SG\getFirst(post('start'),0);
		$this->userId = post('u');
		$this->projectId = post('id');
	}

	function build() {
		// Data Model
		$showItems = 10;
		$uid = i()->uid;
		$isAdmin = is_admin('project');

		$conditions = (Object) [
			'userId' => $this->userId,
			'projectId' => $this->projectId,
		];

		$option = (Object) [
			'start' => $this->start,
			'item' => $showItems,
			'actionOrder' => '`trid` DESC',
			'order' => 'ac.`trid` DESC',
			'debug' => false,
		];


		$activity = R::Model('project.action.get2', $conditions, $option);
		$activityCount = count($activity);

		if ($this->userId && empty($this->start)) {
			$userInfo = R::Model('user.get', $this->userId);
		} else if ($this->projectId && empty($this->start)) {
			$projectInfo = R::Model('project.get', $this->projectId, '{data: "info"}');
			$userInfo = R::Model('user.get', $projectInfo->uid);
		}

		return new Scaffold([
			'body' => new Widget([
				'children' => [
					$userInfo && empty($this->start) ? new Container([
						'class' => 'my-profile-wrapper',
						'children' => [
							'<div class="-photo"><img src="'.model::user_photo($userInfo->username).'" width="100%" height="100%" /></a></div>',
							'<div class="-name">'.$userInfo->name.'</div>',
						], // children
					]) : NULL, // Container

					// Show action card render
					new ProjectActionsWidget([
						'children' => $activity,
						'urlMore' => $activityCount && $activityCount == $showItems ? url('project/app/activity', ['u' => $this->userId, 'id' => $this->projectId, 'start' => $this->start+$activityCount]) : NULL,
					]),
					// new Container([
					// 	'children' => (function($activity) {
					// 		$widgets = [];
					// 		foreach ($activity as $rs) {
					// 			$widgets[] = new Card([
					// 				'class' => 'project-activity',
					// 				'id' => 'project-action-'.$rs->actionId,
					// 				'data-url' => url('project/'.$rs->projectId.'/info.action.card/'.$rs->actionId),
					// 				'child' => R::View(
					// 					'project.action.card.render',
					// 					$rs,
					// 					'{page: "'.(R()->appAgent ? 'app' : '').'"}'
					// 				),
					// 			]);
					// 		}
					// 		return $widgets;
					// 	})($activity),
					// ]),

					// Show more button
					// $activityCount && $activityCount == $showItems ? new Container([
					// 	'id' => 'more',
					// 	'class' => 'activity-more',
					// 	'style' => 'padding: 24px 16px 44px;',
					// 	'children' => [
					// 		'<a class="sg-action btn -primary" href="'.url('project/app/activity', ['u' => $this->userId, 'id' => $this->projectId, 'start' => $this->start+$activityCount]).'" data-rel="replace:#more" style="margin:0 auto;display:block;text-align:center; padding: 16px 0;">'
					// 		. '<span>{tr:More}</span>'
					// 		. '<i class="icon -material">chevron_right</i>'
					// 		. '</a>',
					// 	], // children
					// ]) : NULL, // Container
				], // children
			]), // Widget
		]);
	}
}
?>