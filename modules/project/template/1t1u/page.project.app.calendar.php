<?php
/**
* Module :: Description
* Created 2021-01-24
* Modify  2021-01-24
*
* @param Object $self
* @return String
*
* @usage project/app/calendar
*/

$debug = true;

function project_app_calendar($self) {
	$toolbar = new Toolbar($self, 'ปฎิทิน');

	$ret = '';

	$myProject = R::Model('project.follows', '{uid: "member"}');

	if ($myProject->items) {
		$myProjectList = array();
		foreach ($myProject->items as $rs) $myProjectList[] = $rs->projectId;
		$myActivity = R::Model('project.activity.get.bytpid', implode(',', $myProjectList));

		$tables = new Table();
		foreach ($myActivity->items as $rs) {
			$tables->rows[] = array(
				($rs->action_date ? sg_date($rs->action_date, 'ว ดด ปปปป') : '')
				. ($rs->action_time ? ' '.$rs->action_time.' น' : ''),
				$rs->title.'<br /><em><small>'.$rs->projectTitle.'</small></em>',
			);
		}

		$ret .= $tables->build();
	}

	//$ret .= print_o($myProject, '$myProject');
	//$ret .= print_o($myActivity, '$myActivity');
	return $ret;
}
?>