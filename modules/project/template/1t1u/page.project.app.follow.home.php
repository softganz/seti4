<?php
/**
* Project :: App follow project home
* Created 2021-01-27
* Modify  2021-01-27
*
* @param Object $self
* @return String
*
* @usage project/app/follow
*/

$debug = true;

function project_app_follow_home($self) {
	// Data Model

	$cacheData = mydb::select('SELECT `bigId`, `fldData` FROM %bigdata% WHERE `keyName` = "cache" AND `fldName` = "project/app/follow" LIMIT 1');

	if (post('save')) {
		mydb::where('t.`parent` IN (102,3135) AND p.`project_status` = "กำลังดำเนินโครงการ"');

		$projectDbs = mydb::select(
			'SELECT
			p.`tpid` `projectId`, t.`title`
			, tc.`title` `tambonTitle`
			, COUNT(DISTINCT pc.`tpid`) `totalTambon`
			, COUNT(DISTINCT pe.`tpid`) `totalEmployee`
			, COUNT(a.`trid`) `totalAction`
			, COUNT(DISTINCT a.`tpid`) `totalEmployeeAction`
			, COUNT(IF(a.`date1` = CURDATE(), 1, NULL)) `totalTodayAction`
			, COUNT(DISTINCT IF(a.`date1` = CURDATE(), a.`tpid`, NULL)) `totalTodayEmployee`
			, COUNT(IF(a.`date1` = SUBDATE(CURDATE(), 1), 1, NULL)) `totalYesterdayAction`
			, COUNT(DISTINCT IF(a.`date1` = SUBDATE(CURDATE(), 1), a.`tpid`, NULL)) `totalYesterdayEmployee`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %topic% tc ON tc.`parent` = p.`tpid`
				LEFT JOIN %project% pc ON pc.`tpid` = tc.`tpid` AND pc.`ownertype` = "tambon" AND pc.`project_status` = "กำลังดำเนินโครงการ"
				LEFT JOIN %topic% te ON te.`parent` = pc.`tpid`
				LEFT JOIN %project% pe ON pe.`tpid` = te.`tpid` AND pe.`project_status` = "กำลังดำเนินโครงการ"
				LEFT JOIN %project_tr% a ON a.`tpid` = pe.`tpid` AND `formid` = "activity"
			%WHERE%
			GROUP BY p.`tpid`
			ORDER BY CONVERT(t.`title` USING tis620)
			;
			-- {sum: "totalTambon,totalEmployee,totalAction,totalEmployeeAction,totalTodayAction,totalTodayEmployee,totalYesterdayAction,totalYesterdayEmployee"}
			'
		);
		// debugMsg('<pre>'.mydb()->_query.'</pre>');

		unset($projectDbs->_query, $projectDbs->_vars);
		$data = (Object) [
			'bigId' => $cacheData->bigId,
			'fldData' => SG\json_encode($projectDbs),
			'created' => date('U'),
			'ucreated' => i()->uid,
			'modified' => date('U'),
			'umodified' => i()->uid,
		];
		mydb::query(
			'INSERT INTO %bigdata%
			(`bigId`, `keyName`, `fldName`, `fldType`, `fldData`, `created`, `ucreated`)
			VALUES
			(:bigId, "cache", "project/app/follow", "json", :fldData, :created, :ucreated)
			ON DUPLICATE KEY UPDATE
			`fldData` = :fldData
			, `modified` = :modified
			, `umodified` = :umodified',
			$data
		);
		// debugMsg(mydb()->_query);
	} else {
		$projectDbs = SG\json_decode($cacheData->fldData);
	}


	// // Not Used
	// mydb::where('a.`formid` = "activity"');
	// // ' AND p.`ownertype` IN ("graduate", "student", "people")');
	// $projectDbs = mydb::select(
	// 	'SELECT
	// 	a.`tpid` `projectId`
	// 	-- , university.`title`
	// 	FROM %project_tr% a
	// 		-- LEFT JOIN %project% p ON p.`tpid` = a.`tpid`
	// 		-- LEFT JOIN %topic% t ON t.`tpid` = a.`tpid`
	// 		-- LEFT JOIN %topic% tambon ON tambon.`tpid` = t.`parent`
	// 		-- LEFT JOIN %topic% university ON university.`tpid` = tambon.`parent`
	// 	%WHERE%
	// 	GROUP BY a.`tpid`
	// 	'
	// );


	// // Not Used
	// $projectDbs = mydb::select(
	// 	'SELECT
	// 	t2.*
	// 	, COUNT(te.`tpid`) `totalEmployee`
	// 	FROM (
	// 		SELECT t1.*
	// 		FROM
	// 			( SELECT
	// 			p.`tpid` `projectId`, t.`title`
	// 			, tc.`title` `tambonTitle`
	// 			, COUNT(DISTINCT pc.`tpid`) `totalTambon`
	// 			FROM %project% p
	// 				LEFT JOIN %topic% t USING(`tpid`)
	// 				LEFT JOIN %topic% tc ON tc.`parent` = p.`tpid`
	// 				LEFT JOIN %project% pc ON pc.`tpid` = tc.`tpid` AND pc.`ownertype` = "tambon" AND pc.`project_status` = "กำลังดำเนินโครงการ"
	// 				%WHERE%
	// 				GROUP BY `projectId`
	// 				ORDER BY CONVERT(t.`title` USING tis620)
	// 			) t1
	// 	) t2
	// 	LEFT JOIN %topic% te ON te.`parent` = t2.`projectId`
	// 	GROUP BY `projectId`
	// 	'
	// );

	// debugMsg($projectDbs, '$projectDbs');

	// View Model
	$ret = '';

	$toolbar = new Toolbar($self, 'โครงการ');
	$ret .= '<a class="sg-action" href="'.url('project/app/search').'" data-webview="ค้นหาโครงการ" style="padding: 8px; position: absolute; right: 8px; margin-top: -44px; z-index: 1;"><i class="icon -material" style="color: #fff;">search</i></a>';


	$projectCard = new Ui('div', 'ui-card');

	$projectCard->add(
		'<div class="header"><h3>ภาพรวม</h3></div>'
		. '<div class="detail">'
		.($projectDbs->sum->totalTambon ? 'โครงการ <b>'.number_format($projectDbs->sum->totalTambon).'</b> ตำบล ' : '')
		.($projectDbs->sum->totalEmployee ? 'ผู้รับจ้าง <b>'.number_format($projectDbs->sum->totalEmployee).'</b> คน<br />' : '')
		.'กิจกรรมวันนี้ <b>'.number_format($projectDbs->sum->totalTodayEmployee).'</b> คน <b>'.number_format($projectDbs->sum->totalTodayAction).'</b> กิจกรรม<br />'
		.'กิจกรรมเมื่อวาน <b>'.number_format($projectDbs->sum->totalYesterdayEmployee).'</b> คน <b>'.number_format($projectDbs->sum->totalYesterdayAction).'</b> กิจกรรม<br />'
		. 'กิจกรรมทั้งหมด <b>'.number_format($projectDbs->sum->totalEmployeeAction).'</b> คน <b>'.number_format($projectDbs->sum->totalAction).'</b> กิจกรรม'
		.'</div>',
	);

	foreach ($projectDbs->items as $rs) {
		$url = url('project/app/follow/'.$rs->projectId);
		$cardOption = array(
			'class' => 'sg-action',
			'href' => $url,
			'data-webview' => $rs->title,
		);

		$projectCard->add(
			'<div class="header"><h3><a class="sg-action" href="'.$url.'" data-webview="'.$rs->title.'">'.$rs->title.'</a></h3></div>'
			. '<div class="detail">'
			.($rs->totalTambon ? 'โครงการ <b>'.number_format($rs->totalTambon).'</b> ตำบล ' : '')
			.($rs->totalEmployee ? 'ผู้รับจ้าง <b>'.number_format($rs->totalEmployee).'</b> คน<br />' : '')
			.'กิจกรรมวันนี้ <b>'.number_format($rs->totalTodayEmployee).'</b> คน <b>'.number_format($rs->totalTodayAction).'</b> กิจกรรม<br />'
			.'กิจกรรมเมื่อวาน <b>'.number_format($rs->totalYesterdayEmployee).'</b> คน <b>'.number_format($rs->totalYesterdayAction).'</b> กิจกรรม<br />'
			. 'กิจกรรมทั้งหมด <b>'.number_format($rs->totalEmployeeAction).'</b> คน <b>'.number_format($rs->totalAction).'</b> กิจกรรม'
			.'</div>',
			$cardOption
		);
	}

	$ret .= $projectCard->build();




	//list($setCard, $followCard) = __project_app_follow_list($topicUser);

	//$ret .= $setCard->build();

	//$ret .= $followCard->build();

	return $ret;
}
?>