<?php
/**
* Project Model :: Remove Action
* Created 2018-01-23
* Modify  2021-02-10
*
* @param Int $actionId
* @param Object $options
* @return Object
*
* @usage R::Model("project.action.remove", $actionId, $options)
*/

$debug = true;

function r_project_action_remove($actionId, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'error' => false,
		'msg' => '',
		'query' => NULL,
		'actionId' => NULL,
		'calId' => NULL,
		'info' => (Object) [],
		'calendar' => (Object) [],
	];

	$actionInfo = R::Model('project.action.get', ['actionId' => $actionId], '{debug: false}');

	if (empty($actionInfo->actionId)) {
		$result->error = true;
		$result->msg = 'No activity';
		return $result;
	}

	$projectId = $actionInfo->projectId;
	$actionId = $actionInfo->actionId;

	$result->actionId = $actionId;
	$result->calId = $actionInfo->calid;

	$result->info = $actionInfo;

	$lockReportDate = project_model::get_lock_report_date($projectId);
	if ($actionInfo->actionDate <= $lockReportDate) {
		$result->error = true;
		$result->msg = 'ไม่สามารถแก้ไข/ลบบันทึกกิจกรรมนี้ได้';
		return $result;
	}


	// Start Remove Action

	// Remove Files
	$fileDbs = mydb::select(
		'SELECT * FROM %topic_files%
		WHERE `tpid` = :projectId AND `refid` = :actionId
			AND (`tagname` IN ("project,action", "project,rcv")
				OR (`gallery` IS NOT NULL AND `gallery` = :gallery))',
		':projectId', $projectId,
		':actionId', $actionId,
		':gallery', $actionInfo->gallery
	);

	foreach ($fileDbs->items as $rs) {
		R::Model('photo.delete', $rs->fid);
	}

	// Remove action from project transaction
	$stmt = 'DELETE FROM %project_tr% WHERE `trid` = :actionId LIMIT 1';
	mydb::query($stmt, ':actionId', $actionId);
	$result->query[] = mydb()->_query;

	// Remove expense from project transaction
	mydb::query('DELETE FROM %project_tr% WHERE `calid` = :calid AND `formid` = "expense" AND `part` = "exptr"', ':calid', $actionInfo->calid);
	$result->query[] = mydb()->_query;

	// Remove Trainning
	mydb::query('DELETE FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = "info" AND `part` = "train" AND `parent` = :actionId', ':projectId', $projectId, ':actionId', $actionId);
	$result->query[] = mydb()->_query;

	// Remove Activity And Calendar
	if ($options->removeActivity && $actionInfo->calid) {
			$resultCalendar = R::Model('project.calendar.remove',$actionInfo->calid);
			$result->calendar = $resultCalendar;
			//$ret .= print_o($resultCalendar, '$resultCalendar');
		/*
		if ($actionInfo->calid) {
			$stmt = 'DELETE FROM %calendar% WHERE `id` = :calId LIMIT 1';
			mydb::query($stmt, ':calId', $actionInfo->calid);
			$result->query[] = mydb()->_query;

			$stmt = 'DELETE FROM %project_activity% WHERE `calId` = :calId LIMIT 1';
			mydb::query($stmt, ':calId', $actionInfo->calid);
			$result->query[] = mydb()->_query;
		}

		// Remove Activity
		if ($actionInfo->activityId) {
			$stmt = 'DELETE FROM %project_tr% WHERE `trid` = :activityId LIMIT 1';
			mydb::query($stmt, ':activityId', $actionInfo->activityId);
			$result->query[] = mydb()->_query;
		}
		*/
	}

	$result->msg .= 'ลบบันทึกกิจกรรมเรียบร้อย';

	model::watch_log('project','Action remove','Action id '.$actionId.' of calid '.$action->calid.' was removed from project '.$projectId.' by '.i()->name.'('.i()->uid.')');

	$firebaseCfg = cfg('firebase');
	$firebaseFolder = SG\getFirst($firebaseCfg['update'], 'update');
	$firebase = new Firebase('sg-project-man', $firebaseFolder);
	$firebaseData = array(
		'projectId' => $projectId,
		'actionId' => $actionId,
		'changed' => 'remove',
		'time' => array('.sv' => 'timestamp'),
	);
	$firebase->set($actionId, $firebaseData);
	return $result;
}
?>
