<?php
/**
* Create Project From Development Model
*
* @param Object $data
* @return Object $options
*/

function r_project_develop_follow_create($devInfo, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$tpid = $devInfo->tpid;
	$uid = i()->uid;
	$owner = $devInfo->uid;
	$tagname = 'develop';

	$result = (Object) [
		'extend' => NULL,
		'message' => [
			'<b>CREATE PROJECT FROM DEVELOPMENT</b>',
			'<b>CREATE PROJECT INFORMATION</b>',
		],
	];

	$stmt = 'INSERT IGNORE INTO %project%
		(
		`tpid`, `projectset`, `prtype`, `pryear`, `prid`, `budget`
		, `changwat`, `ampur`, `tambon`, `village`
		, `date_from`, `date_end`, `date_approve`
		, `prtrainer`
		)
		SELECT
		d.`tpid`, t.`parent`, "โครงการ", `pryear`, `prid`, `budget`
		, t.`changwat`, :ampur, :tambon, :village
		, d.`date_from`, d.`date_end`, d.`date_from`
		, (SELECT GROUP_CONCAT(`name`)
				FROM %topic_user% tu
				LEFT JOIN %users% u USING(`uid`)
				WHERE `tpid` = :tpid AND `membership` = "TRAINER"
				GROUP BY `tpid`)
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE `tpid` = :tpid LIMIT 1';

	mydb::query($stmt, $devInfo->info);

	$result->message[] = mydb()->_query;




	$devData = $devInfo->data;;


	// Add other information into project
	$result->message[] = '<b>UPDATE PROJECT INFORMATION</b>';

	$project = [];
	$prphone = [];
	$prteam = [];

	$project['tpid'] = $tpid;
	$project['prowner'] = trim($devData['owner-prename'] . ' ' . $devData['owner-name'] . ' ' . $devData['owner-lastname']);

	if ($devData['owner-phone']) $prphone[] = $devData['owner-phone'];
	if ($devData['owner-mobile']) $prphone[] = $devData['owner-mobile'];
	$project['prphone'] = implode(',', $prphone);

	if ($devData['coowner-1-name']) $prteam[] = trim($devData['coowner-1-prename'] . ' ' . $devData['coowner-1-name'] . ' ' . $devData['coowner-1-lastname']);
	if ($devData['coowner-2-name']) $prteam[] = trim($devData['coowner-2-prename'] . ' ' . $devData['coowner-2-name'] . ' ' . $devData['coowner-2-lastname']);
	if ($devData['coowner-3-name']) $prteam[] = trim($devData['coowner-3-prename'] . ' ' . $devData['coowner-3-name'] . ' ' . $devData['coowner-3-lastname']);
	if ($devData['coowner-4-name']) $prteam[] = trim($devData['coowner-4-prename'] . ' ' . $devData['coowner-4-name'] . ' ' . $devData['coowner-4-lastname']);
	if ($devData['coowner-5-name']) $prteam[] = trim($devData['coowner-5-prename'] . ' ' . $devData['coowner-5-name'] . ' ' . $devData['coowner-5-lastname']);
	$project['prteam'] = implode(' , ', $prteam);

	$project['target'] = 'กลุ่มเป้าหมายหลัก'._NL._NL
		.$devData['project-target']._NL._NL
		.(trim($devData['target-secondary-detail']) != '' ? 'กลุ่มเป้าหมายรอง'._NL._NL . $devData['target-secondary-detail'] : '');
	$project['totaltarget'] = $devData['target-main-total'] + $devData['target-secondary-total'];

	$project['area'] = $devData['project-commune'];

	$stmt = 'UPDATE %project% SET
		`prowner` = :prowner, `prphone` = :prphone, `prteam` = :prteam
		, `target` = :target, `totaltarget` = :totaltarget
		, `area` = :area
		WHERE `tpid` = :tpid LIMIT 1';

	mydb::query($stmt, $project);
	$result->message[] = mydb()->_query;




	// FOR LOCAL FUND ONLY
	if (mydb::columns('project','orgnamedo')) {
		$project['orgnamedo'] = $devInfo->info->orgnamedo;
		$project['supporttype'] = $devInfo->info->category;
		$project['supportorg'] = $devInfo->info->ownergroup;

		$stmt = 'UPDATE %project% SET
			  `orgnamedo` = :orgnamedo
			, `supporttype` = :supporttype
			, `supportorg` = :supportorg
			WHERE `tpid` = :tpid LIMIT 1';

		mydb::query($stmt, $project);
		$result->message[] = mydb()->_query;
	}

	// DONE : ความสอดคล้องกับแผนงาน project_tr:info:supportplan
	// DONE : สถานการณ์ : project_tr:project:problem:1:1
	// DONE : 7.4 กิจกรรมหลักตามกลุ่มเป้าหมายหลัก
	// กลุ่มเป้าหมายหลัก
	// DONE : งบประมาณ คำนวณจากยอดรวมแต่ละกิจกรรม
	// ผลที่คาดว่าจะได้รับ:text5 หลักการและเหตุผล project_tr:info:basic:text1
	// project:activity


	// Add กิจกรรมหลักตามกลุ่มเป้าหมายหลัก to topic parent
	$result->message[] = '<b>ADD MAIN ACTIVITY BY TARGET TO TOPIC PARENT</b>';
		foreach ($devInfo->data as $key => $value) {
			if (preg_match('/^act\-target\-/', $key)) {
				$data = array();
				$data['tpid'] = $tpid;
				list($a, $b, $data['tgtid'], $data['parent']) = explode('-',$key);
				if (empty($value) || $data['tgtid'] == 'other') continue;
				$stmt = 'INSERT IGNORE INTO %topic_parent% (`tpid`, `parent`, `tgtid`) VALUES (:tpid, :parent, :tgtid)';
				mydb::query($stmt, $data);
				$result->message[] = mydb()->_query;
			}
		}



	$result->message[] = '<b>ADD BASIC INFORMATION TO PROJECT_TR</b>';
		$stmt = 'INSERT INTO %project_tr%
						(`tpid`, `formid`, `part`, `uid`, `text1`, `text5`, `created`)
						VALUES
						(:tpid, "info", "basic", :uid, :text1, :text5, :created)';
		mydb::query($stmt, ':tpid', $tpid, ':uid', $uid, ':text1', $devInfo->data['project-problem'], ':text5', $devInfo->data['conversion-human'], ':created', date('U'));
		$result->message[] = mydb()->_query;




	// Set topic type to project and locked topic
	$result->message[] = '<b>UPDATE TOPIC TYPE TO PROJECT</b>';
		$projectStatus = _LOCK; // or _LOCKDETAIL
		mydb::query('UPDATE %topic% SET `type` = "project", `status` = :status WHERE `tpid` = :tpid LIMIT 1', ':tpid', $tpid, ':status', $projectStatus);
		$result->message[] = mydb()->_query;




	// Add Owner into Topic Creater
	$result->message[] = '<b>CREATE TOPIC USER (topic_user)</b>';
		mydb::query('INSERT IGNORE INTO %topic_user% ( `tpid`,`uid`,`membership` ) SELECT `tpid`,`uid`,"OWNER" FROM %topic% WHERE `tpid` = :tpid LIMIT 1', ':tpid', $tpid);
		$result->message[] = mydb()->_query;


	// Set topic type to project and locked topic
	$result->message[] = '<b>UPDATE DEV STATUS TO PASS</b>';
		$projectStatus = _LOCK; // or _LOCKDETAIL
		mydb::query('UPDATE %project_dev% SET `status` = 10 WHERE `tpid` = :tpid LIMIT 1', ':tpid', $tpid);
		$result->message[] = mydb()->_query;


	/*
	// Old version on ชุมชนน่าอยู่
	mydb::query('UPDATE %project_tr% SET `flag` = 1, `num2` = IFNULL(`num3`, 0) + IFNULL(`num4`,0) + IFNULL(`num5`, 0) + IFNULL(`num6`,0) WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "mainact"', ':tpid', $tpid);
	$result->message[] = mydb()->_query;
	*/


	// Add Project Bigdata from Develop Bigdata
	$result->message[] = '<b>CREATE BIGDATA INFORMATION (bigdata, keyname = project.develop => project.info)</b>';
		$stmt = 'INSERT INTO %bigdata%
			( `keyname`, `keyid`, `fldname`, `fldtype`, `flddata`, `created`, `ucreated`, `modified`, `umodified` )
			SELECT "project.info", `keyid`, `fldname`, `fldtype`, `flddata`, UNIX_TIMESTAMP(), :ucreated, NULL, NULL
				FROM %bigdata%
				WHERE `keyid` = :tpid AND `keyname` = "project.develop"
			';
		mydb::query($stmt, ':tpid', $tpid, ':ucreated', $owner);
		$result->message[] = mydb()->_query;




	// Add Project Province from Develop Province
	$result->message[] = '<b>CREATE PROJECT PROVINCE (project_prov , tagname = '.$tagname.' => info)</b>';
		$stmt = 'INSERT INTO %project_prov%
							( `tpid`, `tagname`, `house`, `village`, `tambon`, `ampur`, `changwat`, `areatype` )
							SELECT `tpid`, "info", `house`, `village`, `tambon`, `ampur`, `changwat`, `areatype`
								FROM %project_prov%
								WHERE `tpid` = :tpid AND `tagname` = :tagname
							';
		mydb::query($stmt, ':tpid', $tpid, ':tagname', $tagname);
		$result->message[] = mydb()->_query;


	// Add Project Target From Develop Target
	/*
	$result->message[] = '<h3>Create Target</h3>';
	$oldTarget=mydb::select('SELECT * FROM %project_target% WHERE `tpid`=:tpid AND `tagname`=:tagname ORDER BY `tgtid` ASC;',':tpid',$tpid, ':tagname',$tagname)->items;
	$result->message[] = mydb()->_query;
	foreach ($oldTarget as $rs) {
		$stmt=mydb::create_insert_cmd('project_target',$rs);
		$rs->tagname='info';
		mydb::query($stmt,$rs);
		//$result->message[] = '<p>'.$stmt.'</p>';
		$result->message[] = mydb()->_query;
	}
	$result->message['$oldTarget'] = __project_develop_createproject_table($oldTarget,'$oldTarget');
	*/

	// CREATE PROJECT SUPPORTPLAN
	$result->message[] = '<b>CREATE PROJECT SUPPORTPLAN (project_tr , formid = '.$tagname.' => info , part = supportplan)</b>';
		$devSupportPlan = mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="supportplan" ORDER BY `trid` ASC;', ':tpid', $tpid, ':tagname', $tagname)->items;
		$stmt = mydb::create_insert_cmd('project_tr', reset($devSupportPlan), ':');
		foreach ($devSupportPlan as $rs) {
			$newRs = array();
			foreach ($rs as $key => $value) {
				$newRs[$key.':'] = $value;
			}
			$newRs['trid:'] = NULL;
			$newRs['formid:'] = 'info';
			$newRs['created:'] = date('U');
			$newRs['modified'] = NULL;
			$newRs['modifyby'] = NULL;
			mydb::query($stmt,$newRs);
			$result->message[] = mydb()->_query;
		}
		$result->message['$devSupportPlan'] = mydb::printtable($devSupportPlan);




	/*
	// Add SINGLE main target and amount to project target
	$result->message[] = '<b>ADD MAIN TARGET TO PROJECT TARGET</b>';
		if ($devInfo->info->targetgroup) {
			$data = array();
			$stmt = 'INSERT IGNORE INTO %project_target% (`tpid`, `tagname`, `tgtid`, `amount`) VALUES (:tpid, :tagname, :tgtid, :amount)';
			mydb::query($stmt, ':tpid', $tpid, ':tagname', 'info', ':tgtid', $devInfo->info->targetgroup, ':amount', $devInfo->data['target-main-total']);
			$result->message[] = mydb()->_query;
		}
	*/



	// CREATE PROJECT TARGET
	$result->message[] = '<b>CREATE PROJECT TARGET (project_target , tagname = '.$tagname.' => info</b>';
		$devTarget = mydb::select('SELECT * FROM %project_target% WHERE `tpid` = :tpid AND `tagname` LIKE :tagname;',':tpid', $tpid, ':tagname', $tagname.'%')->items;
		$stmt = mydb::create_insert_cmd('project_target', reset($devTarget), ':');

		foreach ($devTarget as $rs) {
			$newRs = array();
			foreach ($rs as $key => $value) {
				$newRs[$key.':'] = $value;
			}

			list($a1, $subkey) = explode(':', $rs->tagname);
			$newRs['tagname:'] = 'info'.($subkey ? ':'.$subkey : '');

			mydb::query($stmt, $newRs);

			$result->message[] = mydb()->_query;
		}

		$result->message['$devTarget'] = mydb::printtable($devTarget);




	// CREATE PROJECT PROBLEM
	$result->message[] = '<b>CREATE PROJECT PROBLEM (project_tr , formid = '.$tagname.' => info , part = problem)</b>';
		$devProblem = mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="problem" ORDER BY `trid` ASC; -- {key:"trid"}',':tpid',$tpid, ':tagname',$tagname)->items;
		$stmt = mydb::create_insert_cmd('project_tr', reset($devProblem), ':');
		foreach ($devProblem as $rs) {
			$newRs = array();
			foreach ($rs as $key => $value) {
				$newRs[$key.':'] = $value;
			}
			$newRs['trid:'] = NULL;
			$newRs['formid:'] = 'info';
			$newRs['created:'] = date('U');
			$newRs['modified'] = NULL;
			$newRs['modifyby'] = NULL;
			mydb::query($stmt,$newRs);
			$result->message[] = mydb()->_query;
		}
		$result->message['$devProblem'] = mydb::printtable($devProblem);




	// Copy Objective
	$result->message[] = '<b>CREATE PROJECT OBJECTIVE  (project_tr , formid = '.$tagname.' => info , part = objective)</b>';
		$oldObjective=mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="objective" ORDER BY `trid` ASC; -- {key:"trid"}',':tpid',$tpid, ':tagname',$tagname)->items;
		foreach ($oldObjective as $rs) {
			$trid=$rs->trid;
			$rs->trid = NULL;
			$stmt = mydb::create_insert_cmd('project_tr',$rs,':');
			$rs->formid = 'info';
			unset($newRs);
			foreach ($rs as $key => $value) {
				$newRs[$key.':'] = $value;
			}
			mydb::query($stmt,$newRs);
			$newObjectiveId[$trid] = mydb()->insert_id;
			//$result->message[] = '<p>'.$stmt.'</p>';
			$result->message[] = mydb()->_query;
		}
		$result->message['$newObjectiveId'] = $newObjectiveId;
		$result->message['$oldObjective'] = mydb::printtable($oldObjective);



	// Copy Development Indicator to Project Indicator
	$result->message[] = '<b>CREATE PROJECT OBJECTIVE INDICATOR (project_tr , formid = '.$tagname.' => info , part = indicator)</b>';
		$oldIndicator = mydb::select('SELECT * FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "indicator" ORDER BY `trid` ASC; -- {key:"trid"}', ':tpid', $tpid, ':tagname', $tagname)->items;
		foreach ($oldIndicator as $rs) {
			$trid = $rs->trid;
			unset($rs->trid);
			$stmt = mydb::create_insert_cmd('project_tr', $rs, ':');
			$rs->formid = 'info';
			$rs->parent = $newObjectiveId[$rs->parent];
			unset($newRs);
			foreach ($rs as $key => $value) {
				$newRs[$key.':'] = $value;
			}
			mydb::query($stmt, $newRs);
			//$result->message[] = '<p>'.$stmt.'</p>';
			$result->message[] = mydb()->_query;
		}
		$result->message['$oldIndicator'] = mydb::printtable($oldIndicator);




	// Copy Development Activity to Project Activity
	$result->message[] = '<b>CREATE PROJECT ACTIVITY (project_tr , formid = '.$tagname.' => info , part = activity)</b>';
		$devActivity = mydb::select('SELECT * FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "activity" ORDER BY `parent`,`sorder` ASC; -- {key:"trid"}', ':tpid', $tpid, ':tagname', $tagname)->items;
		foreach ($devActivity as $rs) {
			$activity = $devInfo->activity[$rs->trid];

			// Create project activity from all develop activity
			//if ($activity->expense) continue;

			$stmt = mydb::create_insert_cmd('project_tr',$rs,':');
			//$rs->parent=$newObjectiveId[$rs->parent];
			unset($newRs);
			foreach ($rs as $key => $value) {
				$newRs[$key.':'] = $value;
			}
			$newRs['trid:'] = NULL;
			$newRs['formid:'] = 'info';
			$newRs['created:'] = date('U');
			$newRs['modified'] = NULL;
			$newRs['modifyby'] = NULL;
			mydb::query($stmt, $newRs);
			$newActivityId[$rs->trid] = mydb()->insert_id;
			//$result->message[] = '<p>'.$stmt.'</p>';
			$result->message[] = mydb()->_query;
		}

		foreach ($newActivityId as $oldid => $id) {
			$activity = $devInfo->activity[$oldid];
			if (is_null($activity->parent)) continue;
			$stmt = 'UPDATE %project_tr% SET `parent` = :parent WHERE `trid` = :trid LIMIT 1';
			mydb::query($stmt, ':trid', $id, ':parent', $newActivityId[$activity->parent]);
			$result->message[] = mydb()->_query;
		}
		$result->message['$newActivityId'] = $newActivityId;
		$result->message['$devActivity'] = mydb::printtable($devActivity);





	// Create Activity Calendar
	$result->message[] = '<b>CREATE PROJECT CALENDAR (project_tr , formid = '.$tagname.' => info , part = activity => calendar)</b>';
		foreach ($devActivity as $rs) {
			$activity = $devInfo->activity[$rs->trid];
			//$result->message[] = $activity;

			// แนวคิดเดิมคือสร้างปฏิทินเฉพาะกิจกรรมที่ระบุค่าใช้จ่ายเท่านั้น
			// แนวคิดใหม่ เอาเฉพาะที่กำหนด budget นำมาใส่ไว้ในปฏิทินทั้งหมด
			//if (empty($activity->budget)) continue;

			//$result->message[] = '<font color="red">Add calendar</font>'.$parent.$newActivityId[$parent];

			$calData = (Object) [];
			$calData->tpid = $tpid;
			$calData->title = $rs->detail1;
			$calData->from_date = $rs->date1;
			$calData->to_date = $rs->date2;
			$calData->privacy = 'public';
			$calData->calowner = 1;
			$calData->owner = $rs->uid;
			$calData->village = NULL;
			$calData->tambon = NULL;
			$calData->ampur = NULL;
			$calData->changwat = NULL;
			$calData->detail = $rs->text1;
			$calData->ip = GetEnv('REMOTE_ADDR');
			$calData->created_date = date('Y-m-d H:i:s');

			$stmt = mydb::create_insert_cmd('calendar', $calData);
			mydb::query($stmt, $calData);
			$rs->calid = mydb()->insert_id;

			//$result->message[] = '<p>'.$stmt.'</p>';
			$result->message[] = mydb()->_query;

			/*
			$trid = $rs->trid;
			$parent = $rs->parent;
			$rs->formid = 'info';
			$rs->mainact = $rs->parent = $newActivityId[$parent];
			*/

			$rs->calowner = 1;
			$rs->mainact = $newActivityId[$activity->parent];
			$rs->budget = SG\getFirst($rs->num1, 0);
			$rs->targetpreset = 0;
			$actStmt = mydb::create_insert_cmd('project_activity', $rs);
			mydb::query($actStmt, $rs);
			//$result->message[] = '<p>'.$actStmt.'</p>';
			$result->message[] = mydb()->_query;

			$stmt = 'UPDATE %project_tr% SET `calid` = :calid WHERE `trid` = :trid LIMIT 1';
			mydb::query($stmt, ':trid', $newActivityId[$rs->trid], ':calid', $rs->calid);
			$result->message[] = mydb()->_query;

			//$result->message[] = print_o($rs, '$rs');
		}




	$result->message[] = '<b>CREATE PROJECT ACTIVITY OBJECTIVE (project_tr , formid = '.$tagname.' => info , part = actobj)</b>';
		$oldActobj=mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="actobj" ORDER BY `trid` ASC; -- {key:"trid"}',':tpid',$tpid, ':tagname',$tagname)->items;
		foreach ($oldActobj as $rs) {
			$trid=$rs->trid;
			unset($rs->trid);
			$stmt=mydb::create_insert_cmd('project_tr',$rs,':');
			$rs->formid='info';
			$rs->parent=$newObjectiveId[$rs->parent];
			$rs->gallery=$newActivityId[$rs->gallery];
			unset($newRs);
			foreach ($rs as $key => $value) {
				$newRs[$key.':']=$value;
			}
			mydb::query($stmt,$newRs);
			//$result->message[] = '<p>'.$stmt.'</p>';
			$result->message[] = mydb()->_query;
		}
		$result->message['$oldActobj'] = mydb::printtable($oldActobj);




	$result->message[] = '<b>CREATE PROJECT ACTIVITY EXPENSE (project_tr , formid = '.$tagname.' => info , part = exptr)</b>';
		$oldExptr=mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="exptr" ORDER BY `trid` ASC; -- {key:"trid"}',':tpid',$tpid, ':tagname',$tagname)->items;
		foreach ($oldExptr as $rs) {
			$trid=$rs->trid;
			unset($rs->trid);
			$stmt=mydb::create_insert_cmd('project_tr',$rs,':');
			$rs->formid='info';
			$rs->parent=$newActivityId[$rs->parent];
			unset($newRs);
			foreach ($rs as $key => $value) {
				$newRs[$key.':']=$value;
			}
			mydb::query($stmt,$newRs);
			//$result->message[] = '<p>'.$stmt.'</p>';
			$result->message[] = mydb()->_query;
		}
		$result->message['$oldExptr'] = mydb::printtable($oldExptr);


	/*
	$result->message[] = '<b>CREATE PROJECT ACTIVITY TARGET (project_target, tagname = '.$tagname.':mainact => project:mainact)</b>';
	$oldTarget=mydb::select('SELECT * FROM %project_target% WHERE `tpid`=:tpid AND `tagname`=:tagname ORDER BY `tgtid` ASC;',':tpid',$tpid, ':tagname',$tagname.':mainact')->items;
	$result->message[] = mydb()->_query;
	foreach ($oldTarget as $rs) {
		$stmt=mydb::create_insert_cmd('project_target',$rs);
		$rs->tagname='project:mainact';
		$rs->trid=$newActivityId[$rs->trid];
		mydb::query($stmt,$rs);
		//$result->message[] = '<p>'.$stmt.'</p>';
		$result->message[] = mydb()->_query;
	}
	$result->message['$oldTarget'] = mydb::printtable($oldTarget);
	*/






	/*
	$devActivity=mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" ORDER BY `trid` ASC; -- {key:"trid"}',':tpid',$tpid, ':tagname',$tagname)->items;
	foreach ($devActivity as $rs) {
		$trid=$rs->trid;
		unset($rs->trid);
		$rs->formid='info';
		$rs->mainact=$rs->parent=$newActivityId[$rs->parent];
		$rs->budget=$rs->num1;
		$rs->targetpreset=0;
		//$newActivityId[$rs->parent];
		$rs->title=$rs->detail1;
		$rs->from_date=$rs->date1;
		$rs->to_date=$rs->date2;
		$rs->privacy='public';
		$rs->calowner=1;

		$stmt=mydb::create_insert_cmd('calendar',$rs,':');
		unset($newRs);
		foreach ($rs as $key => $value) {
			$newRs[$key.':']=$value;
		}
		mydb::query($stmt,$newRs);
		$rs->calid=mydb()->insert_id;
		$result->message[] = '<p>'.$stmt.'</p>';
		$result->message[] = '<p>'.mydb()->_query.'</p>';

		$newRs['calid:']=$rs->calid;
		$actStmt=mydb::create_insert_cmd('project_activity',$rs,':');
		mydb::query($actStmt,$newRs);
		$result->message[] = '<p>'.$actStmt.'</p>';
		$result->message[] = '<p>'.mydb()->_query.'</p>';
		$result->message[] = print_o($rs,'$rs');
		$result->message[] = print_o($newRs,'$newRs');
	}
	$result->message[] = print_o($oldExptr,'$oldExptr');
	*/



	// Update Project Budget
	$result->message[] = '<b>UPDATE PROJECT BUDGET (project)</b>';
	mydb::query('UPDATE %project% SET `budget` = :budget WHERE `tpid` = :tpid', ':tpid', $tpid, ':budget', $devInfo->info->budget);
	$result->message[] = mydb()->_query;

	$result->extend = R::Model('project.develop.follow.create.extend',$devInfo);




	$result->message[] = '<a href="'.url('project/'.$tpid).'" target="_blank">ติดตามโครงการ</a>';

	return $result;
}

?>