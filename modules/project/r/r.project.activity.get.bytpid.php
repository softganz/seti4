<?php
/**
 * Get project activity by tpid
 * @param
 * @return Data Set
 */
function r_project_activity_get_bytpid($tpid=NULL, $options='{}', $prowner=NULL, $period=NULL,$para=array()) {
	if (is_object($tpid)) $tpid = $tpid->tpid;
	//if (is_string($options)) debugMsg($options);
	$options = SG\json_decode($options);
	$debug = $options->debug;
	//debugMsg($options,'$options');

	if (empty($tpid)) return;

	$order=SG\getFirst($options->order,'`action_date` ASC');
	if ($options->period) $periodInfo=project_model::get_period($tpid,$options->period);

	mydb::where('`tpid` IN (:tpid)', ':tpid','SET:'.$tpid);
	mydb::where('`formid`=:formid', ':formid','activity');
	if ($options->trid) mydb::where('`trid` IN (:trid)',':trid','SET:'.$options->trid);

	if (is_string($options->owner)) {
		mydb::where('`part`=:prowner', ':prowner',$options->owner);
	} else if (is_numeric($options->owner)) {
		mydb::where('`part`=:prowner', ':prowner',$options->owner==_PROJECT_OWNER_ACTIVITY?'owner':'trainer');
	} else if (is_array($options->owner)) {
		mydb::where('trid IN (:activityArray)',':activityArray',$options->owner);
	}
	if ($options->period) {
		$fromDate=$periodInfo->report_from_date?$periodInfo->report_from_date:$periodInfo->from_date;
		$toDate=$periodInfo->report_to_date?$periodInfo->report_to_date:$periodInfo->to_date;
		mydb::where('`date1` BETWEEN :fromdate AND :todate',':fromdate',$fromDate,':todate',$toDate);
	}

	if ($options->dateFrom) {
		mydb::where('`date1` >= :dateFrom',':dateFrom',$options->dateFrom);
	}

	if ($options->dateEnd) {
		mydb::where('`date1` <= :dateEnd',':dateEnd',$options->dateEnd);
	}

	/*
	if ($para['owner']) $where=sg::add_condition($where,'tr.`uid` IN (:uid)','uid',is_array($para['owner'])?implode(',',$para['owner']):$para['owner']);
	if ($para['year']) $where=sg::add_condition($where,'YEAR(tr.`date1`)=:year','year',$para['year']);
	if ($para['month']) $where=sg::add_condition($where,'tr.`date1` BETWEEN "'.$para['month'].'-01" AND "'.$para['month'].'-30"');
	*/

	/*
	if ($para['changwat']) $where=sg::add_condition($where,'pv.`changwat`=:changwat','changwat',$para['changwat']);
	if ($para['ampur']) $where=sg::add_condition($where,'pv.`ampur`=:ampur','ampur',$para['ampur']);
	if ($para['tambon']) $where=sg::add_condition($where,'pv.`tambon`=:tambon','tambon',$para['tambon']);
	*/
	/*
	num1=ค่าตอบแทน, num2=ค่าจ้าง, num3=ค่าใช้สอย, num4=ค่าวัสดุ, num5=ค่าสาธารณูปโภค, num6=อื่น ๆ
	*/
	$stmt = 'SELECT
		tr.`tpid`, tr.`trid`, tr.`refid`, tr.`parent`
		, tr.`calid`, tr.`gallery`
		, tr.`formid`, tr.`period`, tr.`part`, tr.`flag`, tr.`uid`,
		c.`title`,
		t.`title` projectTitle,
		tr.`rate1` rate,
		tr.`date1` action_date,
		tr.`detail1` action_time,
		a.`budget`,
		a.`mainact`,
		m.`detail1` mainact_detail,
		a.`targetpreset`,
		a.`target` target,
		tr.`text3` targetPresetDetail,
		tr.`num8` targetjoin,
		tr.`text9` targetjoindetail,
		tr.`detail3` objective,
		tr.`text1` goal_do,
		tr.`text2` real_do,
		m.`text3` `presetOutputOutcome`,
		tr.`text4` real_work,
		tr.`text5` problem,
		tr.`text6` recommendation,
		tr.`text7` support,
		tr.`text8` followerrecommendation,
		tr.`detail2` followername,
		tr.`num1` exp_meed,
		tr.`num2` exp_wage,
		tr.`num3` exp_supply,
		tr.`num4` exp_material,
		tr.`num5` exp_utilities,
		tr.`num6` exp_other,
		tr.`num7` exp_total,
		c.`detail` goal_dox,
		u.`username`, u.`name` ownerName,
		GROUP_CONCAT(DISTINCT p.`fid`, "|" , p.`file`) photos
		FROM
			(
			SELECT *
				FROM %project_tr%
				%WHERE%
			) tr
			LEFT JOIN %topic% t ON t.`tpid`=tr.`tpid`
			LEFT JOIN %users% u ON u.`uid`=tr.`uid`
			LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
			LEFT JOIN %project_activity% a ON a.`calid`=tr.`calid`
			LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
			LEFT JOIN %topic_files% p
				ON tr.`gallery` IS NOT NULL
				AND p.`tpid`=tr.`tpid`
				AND p.`gallery`=tr.`gallery`
				AND p.`type`="photo"
		GROUP BY tr.`trid`
		ORDER BY '.$order.';
		-- {key:"trid"}';

	$dbs = mydb::select($stmt);

	if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');

	//debugMsg('<pre>'.mydb()->_query.'</pre>');

	return $dbs;
}
?>