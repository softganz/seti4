<?php
/**
* Project Model :: Get Project Action by tpid
* Created 2019-03-12
* Modify  2021-10-19
*
* @param Object $conditions
* @param Object $options
* @return Data Set
*/

$debug = true;

function r_project_action_get($conditions = NULL, $options = '{}') {
	return R::Model('project.action.get2', $conditions, $options);

	$defaults = '{debug:false, includePhoto: true, key: "actionId", actionOrder: null, order:"`actionDate` ASC, `actionId` ASC", start :-1, item: 0, resultGroup: null}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (Object) $conditions;
	else if (is_string($conditions) && preg_match('/^{/',$conditions)) $conditions = SG\json_decode($conditions);
	else $conditions = (Object) ['projectId' => $conditions];


	$conditions->projectId = SG\getFirst($conditions->projectId, $conditions->tpid);
	$conditions->actionId = SG\getFirst($conditions->actionId, $conditions->actionid);
	$conditions->userId = SG\getFirst($conditions->userId, $conditions->uid);

	$result = NULL;

	if ($debug) debugMsg($conditions,'$conditions');
	if ($debug) debugMsg($options,'$options');

	mydb::value('$ACTIONORDER$', $options->actionOrder ? 'ORDER BY '.$options->actionOrder : '');
	mydb::value('$LIMIT$', $options->start != -1 && $options->item ? 'LIMIT '.$options->start.','.$options->item : '');
	mydb::value('$ORDER$',$options->order);
	mydb::value('$KEY$', $options->key);

	mydb::where('tr.`formid` = :formid', ':formid','activity');
	if ($conditions->projectId) mydb::where('tr.`tpid` IN (:tpid)', ':tpid','SET:'.$conditions->projectId);
	if ($conditions->actionId) mydb::where('tr.`trid` IN (:actionId)',':actionId', 'SET:'.$conditions->actionId);
	if ($conditions->userId) mydb::where('tr.`uid` = :userId',':userId',$conditions->userId);
	if ($conditions->part) mydb::where('tr.`part` = :part',':part',$conditions->part);

	if ($conditions->dateFrom && $conditions->dateEnd) {
		mydb::where('tr.`date1` BETWEEN :fromdate AND :todate',':fromdate',$conditions->dateFrom,':todate',$conditions->dateEnd);
	}

	// Get from child of
	if ($conditions->childOf) {
		$childOfList = mydb::select(
			'SELECT p.`tpid` FROM %project% p LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			WHERE t.`parent` = :childOfId;
			-- {reset: false}',
			// Backward compatible for mydb
			':childOfId', $conditions->childOf
		)->lists->text;
		if ($childOfList) {
			mydb::where('tr.`tpid` IN ( :childOfList )', ':childOfList', 'SET:'.$childOfList);
		} else {
			return $result;
		}
	}

	// Get from areacode
	if ($conditions->changwat) {
		$changwatList = mydb::select(
			'SELECT p.`tpid` FROM %project% p LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			WHERE LEFT(t.`areacode`, 2) = :changwatId;
			-- {reset: false}',
			[':changwatId' => $conditions->changwat]
		)->lists->text;
		if ($changwatList) {
			mydb::where('tr.`tpid` IN ( :changwatList )', ':changwatList', 'SET:'.$changwatList);
		} else {
			return $result;
		}
	}
	if ($conditions->ampur) {
		$ampurList = mydb::select(
			'SELECT p.`tpid` FROM %project% p LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			WHERE LEFT(t.`areacode`, 4) = :ampurId;
			-- {reset: false}',
			[':ampurId' => $conditions->ampur]
		)->lists->text;
		if ($ampurList) {
			mydb::where('tr.`tpid` IN ( :ampurList )', ':ampurList', 'SET:'.$ampurList);
		} else {
			return $result;
		}
	}
	if ($conditions->tambon) {
		$tambonList = mydb::select(
			'SELECT p.`tpid` FROM %project% p LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			WHERE LEFT(t.`areacode`, 6) = :tambonId;
			-- {reset: false}',
			[':tambonId' => $conditions->tambon]
		)->lists->text;
		if ($tambonList) {
			mydb::where('tr.`tpid` IN ( :tambonList )', ':tambonList', 'SET:'.$tambonList);
		} else {
			return $result;
		}
	}

	if ($conditions->projectId && $conditions->period) {
		$periodInfo = project_model::get_period($conditions->projectId,$conditions->period);
		$fromDate = $periodInfo->report_from_date?$periodInfo->report_from_date:$periodInfo->from_date;
		$toDate = $periodInfo->report_to_date?$periodInfo->report_to_date:$periodInfo->to_date;
		mydb::where('tr.`date1` BETWEEN :fromdate AND :todate',':fromdate',$fromDate,':todate',$toDate);
	}

	/*
	if ($para['owner']) $where=sg::add_condition($where,'tr.`uid` IN (:userId)','userId',is_array($para['owner'])?implode(',',$para['owner']):$para['owner']);
	if ($para['year']) $where=sg::add_condition($where,'YEAR(tr.`date1`)=:year','year',$para['year']);
	if ($para['month']) $where=sg::add_condition($where,'tr.`date1` BETWEEN "'.$para['month'].'-01" AND "'.$para['month'].'-30"');

	num1=ค่าตอบแทน, num2=ค่าจ้าง, num3=ค่าใช้สอย, num4=ค่าวัสดุ, num5=ค่าสาธารณูปโภค, num6=อื่น ๆ
	*/

	if ($options->includePhoto) {
		mydb::value('$INCLUDEPHOTO$', '		, ( SELECT GROUP_CONCAT(`fid`,"|",ph.`file`,"|",IFNULL(ph.`tagname`,""))
				FROM %topic_files% ph
				WHERE (ph.`type` = "photo" AND (ph.`tagname` = "project,action" AND ph.`refid` = ac.`trid`) OR ph.`gallery` = ac.`gallery`) ) `photos`
		, ( SELECT COUNT(*)
				FROM %topic_files%
				WHERE `tpid` = ac.`tpid` AND `refid` = ac.`trid` AND `tagname` = "project,rcv") `rcvPhotos`', false);
	} else {
		mydb::value('$INCLUDEPHOTO$', '');
	}

	//TODO: Slow query when option includePhoto

	// mydb::value('$INCLUDEPHOTO$', '');

	$stmt = 'SELECT
		  ac.`tpid` `projectId`
		, ac.`tpid`
		, ac.`trid` `actionId`
		, p.`project_status` `projectStatus`
		, p.`project_status`+0 `projectStatusCode`
		, p.`prtype`
		, ac.`parent` `activityParent`
		, ac.`refid` `activityId`
		, planact.`refcode` `serieNo`
		, ac.`calid`
		, ac.`gallery`
		, ac.`formid`
		, ac.`period`
		, ac.`part`
		, ac.`flag`
		, ac.`uid`
		, IFNULL(c.`title`,planact.`detail1`) `title`
		, t.`title` `projectTitle`
		, tp.`title` `parentTitle`
		, ac.`rate1` `rate1`
		, ac.`rate2` `rate2`
		, DATE_FORMAT(ac.`date1`, "%Y-%m") `actionMonth`
		, ac.`date1` `actionDate`
		, ac.`detail1` `actionTime`
		, a.`budget` `budgetPreset`
		, a.`mainact` `mainactId`
		, m.`detail1` `mainactDetail`
		, a.`targetpreset` `targetPresetAmt`
		, a.`target` `targetPresetDetail`
		--	, ac.`text3` `targetPresetDetail`
		, ac.`num8` `targetJoinAmt`
		, ac.`text9` `targetJoinDetail`
		, ac.`detail3` `objectiveDetail`
		, IFNULL(planact.`text1`, c.`detail`) `actionPreset`
		, ac.`text2` `actionReal`
		, m.`text3` `outputOutcomePreset`
		, ac.`text4` `outputOutcomeReal`
		, ac.`text5` `problem`
		, ac.`text6` `recommendation`
		, ac.`text7` `support`
		, ac.`text8` `followerRecommendation`
		, ac.`detail2` `followerName`
		, ac.`detail4` `jobType`
		, ac.`num1` `exp_meed`
		, ac.`num2` `exp_wage`
		, ac.`num3` `exp_supply`
		, ac.`num4` `exp_material`
		, ac.`num5` `exp_utilities`
		, ac.`num6` `exp_other`
		, ac.`num9` `exp_travel`
		, ac.`num7` `exp_total`
		, c.`detail` `goal_dox`
		, u.`username`, u.`name` `ownerName`
		, mu.`name` `modifybyname`
		, ac.`appsrc`
		, ac.`appagent`
		, FROM_UNIXTIME(ac.`created`,"%Y-%m-%d %H:%i:%s") `created`
		, FROM_UNIXTIME(ac.`modified`,"%Y-%m-%d %H:%i:%s") `modified`
		$INCLUDEPHOTO$
		, ( SELECT GROUP_CONCAT(`trid`)
				FROM %project_tr%
				WHERE `tpid` = ac.`tpid` AND `formid` = "info" AND `part` = "train" AND `parent` = ac.`trid`
			) `trainList`
		FROM
			(
			SELECT tr.*
				FROM %project_tr% tr
				%WHERE%
				$ACTIONORDER$
				$LIMIT$
			) ac
			LEFT JOIN %topic% t ON t.`tpid` = ac.`tpid`
			LEFT JOIN %project% p ON p.`tpid` = ac.`tpid`
			LEFT JOIN %topic% tp ON tp.`tpid` = t.`parent`
			LEFT JOIN %users% u ON u.`uid` = ac.`uid`
			LEFT JOIN %project_tr% planact ON planact.`trid` = ac.`refid`
			LEFT JOIN %calendar% c ON c.`id` = ac.`calid`
			LEFT JOIN %project_activity% a ON a.`calid` = ac.`calid`
			LEFT JOIN %project_tr% m ON m.`trid` = a.`mainact`
			LEFT JOIN %users% mu ON mu.`uid` = ac.`modifyby`
		ORDER BY $ORDER$;
		-- {key:"'.$options->key.'", group: "'.$options->resultGroup.'"}';

	$result = mydb::select($stmt)->items;

	// debugMsg(mydb()->_query);

	// if ($options->includePhoto) {
	// 	foreach ($result as $rs) {
	// 		// debugMsg($rs,'$rs');
	// 		$tridResult[] = $rs->trid;

	// 	}
	// 	debugMsg($tridResult, '$tridResult');
	// 	if ($tridResult) {
	// 		$stmt = 'SELECT COUNT(*)
	// 			FROM %topic_files%
	// 			WHERE `tpid` = ac.`tpid` AND `refid` IN ( :trid ) AND `tagname` = "project,rcv") `rcvPhotos`';
	// 		$dbs = mydb::select($stmt, ':trid', 'SET:'.implode(',', $tridResult));
	// 		debugMsg($dbs,'$dbs');

	// 		$stmt = 'SELECT GROUP_CONCAT(`fid`,"|",ph.`file`,"|",IFNULL(ph.`tagname`,""))
	// 			FROM %topic_files% ph
	// 			WHERE (ph.`type` = "photo" AND (ph.`tagname` = "project,action" AND ph.`refid` = ac.`trid`) OR ph.`gallery` = ac.`gallery`)';
	// 	}
	// }

	if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');
	if ($result && $conditions->actionId) $result = end($result);
	//debugMsg($result, '$result');

	return $result;
}
?>
