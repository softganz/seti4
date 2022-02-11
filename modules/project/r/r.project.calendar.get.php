<?php
/**
* Get calendar of project
* @param Mixed $conditions
* @param String $options
* @return Object or Array Object
*/
function r_project_calendar_get($conditions, $options = '{}') {
	$defaults = '{debug: false, data: "*", order: "c.`from_date` ASC", "start": -1}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;


	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else if (is_numeric($conditions)) {
		$calid = $conditions;
		$conditions = (Object) [
			'calid' => $calid,
		];
	}

	//debugMsg($conditions,'$conditions');

	$result = NULL;

	if ($conditions->calid) {
		mydb::where('c.`id` = :calid', ':calid', $calid);
	} else if ($conditions->activityId) {
		mydb::where('ac.`trid` = :activityId', ':activityId', $conditions->activityId);
	} else {
		if ($conditions->tpid) mydb::where('c.`tpid` = :tpid', ':tpid', $conditions->tpid);

		if (empty($conditions->owner) || $conditions->owner == 1) {
			mydb::where('(a.`calowner` = 1 OR a.`calowner` IS NULL)');
		} else if ($conditions->owner) {
			mydb::where('a.`calowner` = :calowner', ':calowner', $conditions->calowner);
		}

		if ($conditions->period) {
			$periodRs = end(project_model::get_tr($conditions->tpid, 'info:period', $conditions->period)->items['period']);
			mydb::where('c.`from_date` BETWEEN :startdate AND :todate', ':startdate', $periodRs->date1, ':todate' , $periodRs->date2);
		}
	}

	mydb::where(NULL, ':curdate', date('Y-m-d'));
	mydb::value('$order', $options->order);
	/*

		if ($owner=='owner') $calowner=1;
		else if ($owner==2) $calowner=2;
		if ($period) {
			$periodRs=end(project_model::get_tr($tpid,'info:period',$period)->items['period']);
			$periodStr='c.from_date BETWEEN "'.$periodRs->date1.'" AND "'.$periodRs->date2.'"';
		}
	*/

	$stmt = 'SELECT
		ac.`trid` `activityId`
		,  c.`id` `calid`
		, ac.`parent`
		, ac.`refcode` `serieNo`
		, c.*
		, DATE_FORMAT(c.`from_time`, "%H:%i") `from_time`
		, DATE_FORMAT(c.`to_time`, "%H:%i") `to_time`
		, IFNULL(c.`title`,ac.`detail1`) `title`
		, IFNULL(c.`detail`, ac.`text1`) `detail`
		, a.`mainact`
		, a.`budget`
		, a.`targetpreset`
		, a.`target`
		, ac.`tagName`
		, color.`value` `color`
		, action.`trid` `actionId`
		, action.`flag`
		, action.`num7` `exp_total`
		, COUNT(action.`trid`) `trtotal`
		, IF(action.`trid` IS NULL, DATEDIFF(:curdate, c.`to_date`), NULL) `late`
		, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = c.`tpid` AND `formid` = "info" AND `part` = "activity" AND `parent` = ac.`trid`) `childs`
		, (SELECT SUM(`num1`) FROM %project_tr% WHERE `tpid` = c.`tpid` AND `formid` = "info" AND `part` = "activity" AND `parent` = ac.`trid`) `childBudget`
		FROM %calendar% c
			LEFT JOIN %property% color ON color.`propid` = c.`id` AND color.`module` = "calendar" AND color.`name` = "color"
			LEFT JOIN %project_activity% a ON a.`calid` = c.`id`
			LEFT JOIN %project_tr% ac ON ac.`formid` = "info" AND ac.`part` = "activity" AND ac.`calid` = c.`id`
			LEFT JOIN %project_tr% action ON action.`formid` = "activity" AND action.`calid` = c.`id`
			%WHERE%
			GROUP BY `calid`
			ORDER BY $order;';
	$dbs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($conditions->calid || $conditions->activityId) {
		$result = end($dbs->items);
	} else {
		$result = $dbs->items;
	}

	if ($calid && $options->data == '*') {

	}
	//debugMsg(mydb()->_query);
	return $result;
}
?>