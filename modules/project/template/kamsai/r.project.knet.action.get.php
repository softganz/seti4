<?php
/**
 * Get project activity by tpid
 * @param
 * @return Data Set
 */
function r_project_knet_action_get($conditions=NULL, $options='{}') {
	$defaults='{debug:false, key: "actionId", order:"tr.`date1` ASC, tr.`trid` ASC","start":-1}';
	$options=sg_json_decode($options,$defaults);
	$debug=$options->debug;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions=(object)$conditions;
	else if (is_numeric($conditions)) {
		$conditions = (Object) ['tpid' => $conditions];
	}

	$result=NULL;
	if ($debug) debugMsg($conditions,'$conditions');
	//debugMsg($options,'$options');
	//if (empty($tpid)) return $result;

	mydb::value('$ORDER$',$options->order);
	mydb::value('$LIMIT$',($options->start!=-1 && $options->item) ? 'LIMIT '.$options->start.','.$options->item : '');
	mydb::value('$KEY$', $options->key);

	mydb::where('tr.`formid`=:formid', ':formid','activity');
	if ($conditions->tpid) mydb::where('tr.`tpid` IN (:tpid)', ':tpid','SET:'.$conditions->tpid);
	if ($conditions->actionid) mydb::where('tr.`trid` IN (:actionid)',':actionid', 'SET:'.$conditions->actionid);
	if ($conditions->trid) mydb::where('tr.`trid` IN (:trid)',':trid','SET:'.$conditions->trid);
	if ($conditions->uid) mydb::where('tr.`uid`=:uid',':uid',$conditions->uid);
	if ($conditions->part) mydb::where('tr.`part`=:part',':part',$conditions->part);

	if ($conditions->tpid && $conditions->period) {
		$periodInfo=project_model::get_period($conditions->tpid,$conditions->period);
		$fromDate=$periodInfo->report_from_date?$periodInfo->report_from_date:$periodInfo->from_date;
		$toDate=$periodInfo->report_to_date?$periodInfo->report_to_date:$periodInfo->to_date;
		mydb::where('tr.`date1` BETWEEN :fromdate AND :todate',':fromdate',$fromDate,':todate',$toDate);
	}

	$stmt='SELECT
					  ac.`tpid`
					, ac.`trid` `actionId`
					, p.`project_status` `projectStatus`
					, p.`project_status`+0 `projectStatusCode`
					, ac.`parent` `activityParent`
					, ac.`refid` `activityId`
					, ac.`calid`
					, ac.`gallery`
					, ac.`formid`
					, ac.`period`
					, ac.`part`
					, ac.`flag`
					, ac.`uid`
					, c.`title` `title`
					, t.`title` `projectTitle`
					, ac.`rate1` `rate1`
					, ac.`rate2` `rate2`
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
					, IFNULL(ac.`text1`,c.`detail`) `actionPreset`
					, ac.`text2` `actionReal`
					, m.`text3` `outputOutcomePreset`
					, ac.`text4` `outputOutcomeReal`
					, ac.`text5` `problem`
					, ac.`text6` `recommendation`
					, ac.`text7` `support`
					, ac.`text8` `followerRecommendation`
					, ac.`detail2` `followerName`
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
					, FROM_UNIXTIME(ac.`created`,"%Y-%m-%d %H:%i:%s") `created`
					, FROM_UNIXTIME(ac.`modified`,"%Y-%m-%d %H:%i:%s") `modified`
					, (SELECT GROUP_CONCAT(`fid`,"|",ph.`file`,"|",IFNULL(ph.`tagname`,"")) FROM %topic_files% ph WHERE ph.`gallery`=ac.`gallery` AND ph.`type`="photo" ) `photos`
				FROM
					(
					SELECT *
						FROM %project_tr% tr
						%WHERE%
						ORDER BY $ORDER$
						$LIMIT$
					) ac
					LEFT JOIN %topic% t ON t.`tpid`=ac.`tpid`
					LEFT JOIN %project% p ON p.`tpid`=ac.`tpid`
					LEFT JOIN %users% u ON u.`uid`=ac.`uid`
					LEFT JOIN %project_tr% planact ON planact.`trid`=ac.`refid`
					LEFT JOIN %calendar% c ON c.`id`=ac.`calid`
					LEFT JOIN %project_activity% a ON a.`calid`=ac.`calid`
					LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
					LEFT JOIN %users% mu ON mu.`uid`=ac.`modifyby`
				;
				-- {key:"'.$options->key.'"}';
	$result=mydb::select($stmt)->items;
	if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');
	if ($conditions->actionid) $result=end($result);

	/*
	$stmt='SELECT
						tr.*, t.`title` projecttitle, c.`title`
					,	u.`username`, u.`name` poster, mu.`name` modifybyname
					FROM %project_tr% tr
						LEFT JOIN %topic% t ON t.`tpid`=tr.`tpid`
						LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
						LEFT JOIN %users% u ON u.`uid`=tr.`uid`
						LEFT JOIN %users% mu ON mu.`uid`=tr.`modifyby`
					WHERE tr.`formid`=:formid'.($tpid?' AND tr.`tpid`=:tpid':'').($part?' AND (tr.`part`="'.$part.'" '.($isEdit?'':' AND tr.`flag`>0').' ) ':' AND tr.`flag`>0').'
					GROUP BY tr.trid
					ORDER BY '.$order.' '.$sort.'
					'.($part?'':'LIMIT '.$items);
	*/

	return $result;
}
?>