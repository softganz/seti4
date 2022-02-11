<?php
/**
* Model Project Get Information
*
* @param Int $projectId
* @param Object $options
* @return Object
*/

import('model:org.php');

function r_project_get($projectId, $options = '{}') {
	$defaults = '{debug: false, initTemplate: false, type: NULL, data: "info,indicator,objective,activity,expense,bigdata"}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;
	$tagname = 'info';

	if (empty($projectId)) return false;

	$subModuleNameList = array('โครงการ'=>'default','ชุดโครงการ'=>'set','แผนงาน'=>'planning');

	if ($options->initTemplate) R::Module('project.template', $self, $projectId);

	if ($options->type == '*') {
		$stmt = 'SELECT
			  t.`uid`, t.`orgid`, t.`parent`, t.`title`
			, u.`username`
			, u.`name` `ownerName`
			, prset.`title` `projectset_name`
			, prparent.`prtype` `projectParentType`
			, t.`status` `flag`
			, t.`type`
			, p.*
			, p.`project_status`+0 project_statuscode
			, 0 `planBudget`
			, covl.`villname`
			, cosd.`subdistname`
			, cod.`distname`
			, cop.`provname`
			, AsText(p.`location`) location, X(p.`location`) lat, Y(p.`location`) lnt
			, d.`tpid` `proposalId`
			, rev.`revid`
			, rev.`body`
			, t.`created`
			FROM %topic% t
				LEFT JOIN %project% p USING(`tpid`)
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %topic_revisions% rev ON rev.`tpid` = p.`tpid` AND rev.`revid` = t.`revid`
				LEFT JOIN %project_dev% d ON d.`tpid` = p.`tpid`
				LEFT JOIN %topic% prset ON prset.`tpid` = p.`projectset`
				LEFT JOIN %project% prparent ON prparent.`tpid` = t.`parent`
				LEFT JOIN %co_province% cop ON p.`changwat` = cop.`provid`
				LEFT JOIN %co_district% cod ON cod.`distid` = CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
				LEFT JOIN %co_village% covl ON covl.`villid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`, LPAD(p.`village`, 2, "0"))
			WHERE p.`tpid` = :tpid LIMIT 1';
	} else {
		$stmt = 'SELECT
			  t.`uid`, t.`orgid`, t.`parent`, t.`title`
			, u.`username`
			, u.`name` `ownerName`
			, tpParent.`title` `parentTitle`
			, prParent.`prtype` `parentType`
			, prset.`title` `projectset_name`
			, prParent.`prtype` `projectParentType`
			, t.`status` `flag`
			, t.`type`
			, p.*
			, p.`project_status`+0 project_statuscode
			, o.`shortname` orgShortName, o.`name` `orgName`, op.`name` `orgParent`
			, 0 `planBudget`
			, covl.`villname`
			, cosd.`subdistname`
			, cod.`distname`
			, cop.`provname`
			, AsText(p.`location`) location, X(p.`location`) lat, Y(p.`location`) lnt
			, d.`tpid` `proposalId`
			, rev.`revid`
			, rev.`body`
			, rev.`property`
			, t.`created`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %topic_revisions% rev ON rev.`tpid` = p.`tpid` AND rev.`revid` = t.`revid`
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
				LEFT JOIN %project_dev% d ON d.`tpid` = p.`tpid`
				LEFT JOIN %db_org% op ON op.`orgid` = o.`parent`
				LEFT JOIN %topic% prset ON prset.`tpid` = p.`projectset`
				LEFT JOIN %project% prParent ON prParent.`tpid` = t.`parent`
				LEFT JOIN %topic% tpParent ON tpParent.`tpid` = t.`parent`
				LEFT JOIN %co_province% cop ON p.`changwat` = cop.`provid`
				LEFT JOIN %co_district% cod ON cod.`distid` = CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
				LEFT JOIN %co_village% covl ON covl.`villid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`, LPAD(p.`village`, 2, "0"))
			WHERE p.`tpid` = :tpid LIMIT 1';
	}

	$rs = mydb::select($stmt, ':tpid', $projectId);
	// debugMsg('<pre>'.$stmt.'</pre>');
	// debugMsg('<pre>tpid='.$projectId.'<br />'.mydb()->_query.'</pre>');

	if ($rs->_empty) return false;

	if (!$debug) mydb::clearprop($rs);

	//if ($rs->prtype == 'แผนงาน') $tagname = 'planning';


	$result = (Object) [
		'projectId' => $rs->tpid,
		'orgId' => $rs->orgid,
		'title' => $rs->title,
		'tpid' => $rs->tpid,
		'orgid' => $rs->orgid,
		'uid' => $rs->uid,
		'submodule' => $subModuleNameList[$rs->prtype],
		'RIGHT' => NULL,
		'RIGHTBIN' => NULL,
		'info' => $rs,
		'settings' => NULL,
		'membership' => NULL,
		'officers' => NULL,
		'right' => (Object) [],
		'is' => (Object) [],
	];

	$template = array();
	if ($result->info->template)
		$template = array_merge($template, explode(';', $result->info->template));

	$result->settings = sg_json_decode(property('project:SETTING:'.$result->tpid));

	if ($result->info->parent) {
		$result->settings = sg_json_decode($result->settings, property('project:SETTING:'.$result->info->parent));

		$parentTemplate = mydb::select('SELECT * FROM %project% p WHERE `tpid` = :parent LIMIT 1',':parent', $result->info->parent)->template;
		if ($parentTemplate) $template = array_merge($template,explode(';', $parentTemplate));
	}

	$result->info->template = implode(';', array_unique($template));

	$right = 0;

	$result->info->areacode = $result->info->changwat.$result->info->ampur.$result->info->tambon.($result->info->village ? sprintf('%02d',$result->info->village) : '');

	$result->info->areaName = SG\implode_address($result->info);
	if (empty($result->info->area)) $result->info->area = $result->info->areaName;

	if ($result->info->date_from == '0000-00-00')
		$result->info->date_from='';
	if ($result->info->date_end == '0000-00-00')
		$result->info->date_end = '';

	$result->info->lockReportDate = R::Model('project.get.lock.report.date',$projectId);




	// Get topic user of this topic and parent topic
	$membershipList = array();
	$topicUserDbs = mydb::select('SELECT u.*, t.`parent` FROM %topic_user% u LEFT JOIN %topic% t USING(`tpid`) WHERE u.`tpid` IN (:tpid, :parent) ORDER BY IF(`tpid` = :tpid, 1, 0) ASC, `uid`',':tpid',$projectId, ':parent', $result->info->parent);

	foreach ($topicUserDbs->items as $item) {
		$membershipList[$item->uid] = strtoupper($item->membership);
	}
	$result->info->membershipType = $membershipList[i()->uid];
	$result->info->orgMemberShipType = i()->uid ? OrgModel::officerType($result->info->orgid, i()->uid) : NULL;

	if ($result->info->orgid) {
		foreach (mydb::select('SELECT * FROM %org_officer% WHERE `orgid` = :orgid',':orgid',$result->info->orgid)->items as $item) {
			$result->officers[$item->uid] = strtoupper($item->membership);
		}
	}

	// Creater or OWNER membership of topic
	$result->info->isOwner = i()->ok
		&& ($result->info->uid == i()->uid || $result->info->membershipType == 'OWNER');

	// TRAINER membership of topic
	$result->info->isTrainer = i()->ok && $result->info->membershipType == 'TRAINER';

	// Web Admin or ADMIN,MANAGER membership of current project or ADMIN membership of Organization
	$result->info->isAdmin = user_access('administer projects')
		|| (i()->ok && in_array($result->info->membershipType, array('ADMIN','MANAGER')))
		|| (i()->ok && $result->info->orgid
		&& $result->info->orgMemberShipType == 'ADMIN');

	// Creater or OWNER membership of topic or TRINNER membership of topic or right of isAdmin
	$result->info->isRight = $result->info->isOwner
		|| $result->info->isTrainer
		|| $result->info->isAdmin;

	$result->info->isAccess = user_access('access projects') || $result->info->isRight;

	// Project status is still open and isRight or isOwner or isTrainer
	$result->info->isEdit = $result->info->project_statuscode == 1
		&& ($result->info->isRight
		||  $result->info->isOwner
		|| $result->info->isTrainer);

	// isAdmin or right to edit and flag is not lock detail
	$result->info->isEditDetail = $result->info->isAdmin
		|| ($result->info->isEdit && $result->info->flag != _LOCKDETAIL);

	// Set right to RIGHT
	if ($result->info->isAdmin) $right = $right | _IS_ADMIN;
	if ($result->info->isOwner) $right = $right | _IS_OWNER;
	if ($result->info->isTrainer) $right = $right | _IS_TRAINER;
	if ($result->info->isRight) $right = $right | _IS_ACCESS;
	if ($result->info->isEdit) $right = $right | _IS_EDITABLE;
	if ($result->info->isEditDetail) $right = $right | _IS_EDITDETAIL;


	$result->RIGHT = $result->info->RIGHT = $right;
	$result->RIGHTBIN = $result->info->RIGHTBIN = decbin($right);

	$result->membership = $membershipList;

	$result->right->isAdmin = $result->info->isAdmin;
	$result->right->isOwner = $result->info->isOwner;
	$result->right->isTrainer = $result->info->isTrainer;
	$result->right->isRight = $result->info->isRight;
	$result->right->isEdit = $result->info->isEdit;
	$result->right->isEditDetail = $result->info->isEditDetail;


	$result->is->showBudget = $result->info->isAdmin
		|| (
			$result->settings->budget->show
			? (
				($result->settings->budget->show == 'public' || !isset($result->settings->budget->show))
				|| ($result->settings->budget->show == 'member' AND i()->ok)
				|| ($result->settings->budget->show == 'owner' AND $result->info->membershipType == 'OWNER')
				|| ($result->settings->budget->show == 'team' AND $result->info->membershipType)
				|| ($result->settings->budget->show == 'admin' AND $result->info->isAdmin)
				|| ($result->settings->budget->show == 'org' AND $result->info->orgMemberShipType)
			)
		 	: true
		);



	if ($options->data == 'info')
		return $result;

	$result->link = array();
	$stmt = 'SELECT `bigid` `linkId`, `fldref` `projectId`, `flddata` FROM %bigdata% WHERE `keyid` = :tpid AND `keyname` = "project.info" AND `fldname` = "link" ORDER BY `bigid` ASC;
		-- {key: "linkId"}';
		foreach (mydb::select($stmt, ':tpid', $projectId)->items as $rs) {
			$result->link[$rs->linkId] = json_decode($rs->flddata);
			$result->link[$rs->linkId]->linkId = $rs->linkId;
			$result->link[$rs->linkId]->projectId = $rs->projectId;
		}

	// Get Problem
	$stmt = 'SELECT
		  o.`tpid`, o.`trid`
		, o.`refid`
		, o.`tagname`
		, o.`detail1` `problem`
		, o.`text1` `detailproblem`
		, o.`detail2` `objective`
		, o.`text2` `detailobjective`
		, o.`text3` `indicator`
		, o.`num1` `problemsize`
		, o.`num2` `targetsize`
		, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
		FROM %project_tr% o
		WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "problem"
		ORDER BY `trid` ASC;
		-- {key: "trid"}';
	$dbs = mydb::select($stmt,':tpid', $projectId);
	$result->problem = $dbs->items;

	/*
	// Get Objective
	$stmt='SELECT
		o.`tpid`, o.`trid`,
		o.`parent` objectiveType, ot.`name` `objectiveTypeName`,
		o.`text1` title, o.`text2` indicator,
		o.`uid`, o.`created`, o.`modified`, o.`modifyby`
		FROM %project_tr% o
			LEFT JOIN %tag% ot ON ot.`taggroup`="project:objtype" AND ot.`catid`=o.`parent`
		WHERE o.`tpid`=:tpid AND o.`formid`=:tagname AND o.`part`="objective"
		ORDER BY o.`trid` ASC';
	$dbs=mydb::select($stmt,':tpid', $projectId, ':tagname',$tagname);
	foreach ($dbs->items as $rs) {
		$rs->indicator=array();
		$result->objective[$rs->trid]=$rs;
	}
	*/
	// Get Objective
	$stmt = 'SELECT
		  o.`trid` `objectiveId`
		, o.`tpid`
		, o.`trid`
		, o.`refid`
		, o.`refcode` `problemId`
		, o.`parent` `objectiveType`
		, ot.`name` `objectiveTypeName`
		, o.`text1` `title`
		, o.`text2` `indicatorDetail`
		, IFNULL(o.`num1`,pb.`num1`) `problemsize`
		, o.`num2` `targetsize`
		, o.`num3` `outputSize`
		, o.`text4` `outputDetail`
		, o.`text5` `outcomeDetail`
		, o.`text6` `impactDetail`
		, o.`text3` `noticeDetail`
		, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
		FROM %project_tr% o
			LEFT JOIN %tag% ot ON ot.`taggroup` = "project:objtype" AND ot.`catid` = o.`parent`
			LEFT JOIN %project_tr% pb ON (pb.`tpid` = o.`tpid` AND pb.`formid` = :tagname AND pb.`part` = "problem") AND (pb.`tagname` = o.`tagname` AND pb.`refid` = o.`refid` OR pb.`trid` = o.`refcode`)
		WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "objective"
		ORDER BY o.`trid` ASC';
	$dbs = mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname);
	foreach ($dbs->items as $rs) {
		$rs->indicator = array();
		$result->objective[$rs->trid] = $rs;
	}


	// Get Objective Indicator
	$result->indicator = array();
	$stmt = 'SELECT
		  o.`tpid`
		, o.`parent` `objectiveId`
		, o.`formid`, o.`part`
		, o.`tagname`
		, o.`trid` `indicatorId`
		, o.`detail1` `indicatorName`
		, o.`num1` `amount`
		, o.`detail2` `unit`
		, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
		FROM %project_tr% o
		WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "indicator"
		ORDER BY `objectiveId`,o.`tagname`,o.`trid`';
	$dbs=mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname);


	foreach ($dbs->items as $rs) {
		$result->objective[$rs->objectiveId]->indicator[$rs->tagname][] = $rs->indicatorId;
		$result->indicator[$rs->indicatorId] = $rs;
	}



	// Get target
	$stmt = 'SELECT
		  p.`catid` `parentId`, p.`name` `parentName`
		, c.`catid`, c.`name` `targetName`
		, pt.`amount`
		, pt.`joinamt`
		FROM %tag% p
			LEFT JOIN %tag% c ON c.`taggroup` = "project:target" AND c.`catparent` = p.`catid`
			LEFT JOIN %project_target% pt ON pt.`tpid` = :tpid AND pt.`tagname` = :tagname AND pt.`tgtid` = c.`catid`
		WHERE p.`taggroup` = "project:target" AND c.`process` IS NOT NULL AND p.`catparent` IS NULL
		UNION
		SELECT
		  3, NULL
		, t2.`tgtid`, t2.`tgtid`
		, t2.`amount`, t2.`joinamt`
		FROM %project_target% t2
		WHERE t2.`tpid` = :tpid AND t2.`tagname` = "info" AND t2.`tgtid` = 0
		ORDER BY `parentId`, `catid`
		;
		-- {group: "parentId", key: "catid"}';
	$result->target = mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname)->items;

	// Get Plan Activity
	$result->activity = array();
	$stmt = 'SELECT
		  a.`tpid`
		, a.`trid` `activityId`
		, a.`trid`
		, a.`calid`
		, a.`uid`
		, a.`sorder`
		, a.`parent`
		, action.`trid` `actionId`
		, a.`detail1` `title`
		, parent.`detail1` `parentTitle`
		, a.`flag`
		, a.`num1` `budget`
		, a.`num2` `targetpreset`
		, a.`text1` `desc`
		, a.`text2` `indicator`
		, a.`detail2` `timeprocess`
		, a.`date1` `fromdate`
		, a.`date2` `todate`
		, DATEDIFF(:curdate, a.`date2`) `late`
		, a.`text3` `output`
		, a.`text6` `outcome`
		, a.`text4` `copartner`
		, a.`text5` `budgetdetail`
		, a.`detail3` `targetOtherDesc`
		, 0 `totalActitity`
		, 0 `totalBudget`
		, action.`num7` `totalExpense`
	--	, po.`refid`
	--	, pot.`text1` `objectiveTitle2`
		, GROUP_CONCAT(po.`refid`) `objectiveId`
		, GROUP_CONCAT(CONCAT(po.`trid`,"=",po.`refid`) SEPARATOR "|") `objectiveList`
		, GROUP_CONCAT(CONCAT(po.`refid`,"=",IFNULL(pot.`text1`,"")) SEPARATOR "|") `objectiveText`
		, NULL `parentObjectiveId`
		, NULL `parentObjectiveList`
		, NULL `parentObjectiveText`
		, 0 `childsCount`
		, (SELECT GROUP_CONCAT(`trid`) FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "activity" AND `parent` = a.`trid`) `childs`
		, a.`created`
		, a.`modified`
		, a.`modifyby`
		FROM %project_tr% a
			INNER JOIN %calendar% c ON c.`id` = a.`calid`
			LEFT JOIN %project_activity% aty ON aty.`calid` = a.`calid`
			LEFT JOIN %project_tr% parent ON parent.`trid` = a.`parent`
			LEFT JOIN %project_tr% po ON po.`tpid` = a.`tpid` AND po.`parent` = a.`trid` AND po.`formid` = :tagname AND po.`part` = "actobj"
			LEFT JOIN %project_tr% pot ON pot.`trid` = po.`refid`
			LEFT JOIN %project_tr% action ON action.`formid` = "activity" AND action.`refid` = a.`trid`
		WHERE a.`tpid` = :tpid AND a.`formid` = :tagname AND a.`part` = "activity"
		GROUP BY a.`trid`
		ORDER BY
			IF(a.`parent` IS NULL, 0, 1)
			, a.`parent` ASC, `fromdate` ASC, a.`sorder` ASC';

	$dbs = mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname, ':curdate', date('Y-m-d'));
	//debugMsg(mydb()->_query);

	foreach ($dbs->items as $rs) {
		$rs->planBudget = 0;
		$rs->expense = array();
		$rs->childsCount = $rs->childs ? count(explode(',', $rs->childs)) : 0;
		$result->activity[$rs->trid] = $rs;
		if ($rs->parent) $result->info->planBudget += $rs->budget;
	}

	foreach ($dbs->items as $rs) {
		$result->activity[$rs->trid]->planBudget = __project_get_planbudget($result->activity, $rs->trid);
		if ($rs->parent) {
			$result->activity[$rs->trid]->parentObjectiveId = $result->activity[$rs->parent]->objectiveId;
			$result->activity[$rs->trid]->parentObjectiveList = $result->activity[$rs->parent]->objectiveList;
			$result->activity[$rs->trid]->parentObjectiveText = $result->activity[$rs->parent]->objectiveText;
		}
	}


	// Get Plan Expense Transaction
	$stmt = 'SELECT
		  ec.`name` `expName`
		, e.`trid`, e.`parent`, e.`gallery` `costid`
		, e.`num1` amt, e.`num2` `unitprice`, e.`num3` `times`, e.`num4` `total`
		, e.`detail1` `unitname`, e.`text1` detail
		FROM %project_tr% e
			LEFT JOIN %tag% ec ON ec.`taggroup` = "project:expcode" AND ec.`catid` = e.`gallery`
		WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "exptr"
					ORDER BY `trid` ASC';
	$dbs=mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname);
	foreach ($dbs->items as $item) {
		$result->expense[$item->trid] = $item;
		$result->activity[$item->parent]->expense[] = $item->trid;
	}

	$stmt = 'SELECT `bigid`,`keyname`,`fldname`,`fldref`,`flddata` FROM %bigdata% WHERE `keyid` = :tpid AND `keyname` LIKE "project.info%" ORDER BY `fldname` ASC; -- {key: "bigid", group: "fldname"}';
	foreach (mydb::select($stmt,':tpid', $projectId)->items as $item) {
		$keyList = array($item->fldname,$item->bigid);
		if ($item->fldref) $keyList[] = $item->fldref;
		$key = implode('|',$keyList);
		if (count($item) == 1) {
			$item = reset($item);
			$result->bigdata[$item->fldname] = $item->flddata;
		} else if (count($item) > 1) {
			$firstItem = reset($item);
			$result->bigdata[$firstItem->fldname] = $item;
		}
	}

	//print_o($result,'$project',1);

	return $result;
}

function __project_get_planbudget($activity, $trid) {
	$budget = 0;
	foreach ($activity as $rs) {
		if ($rs->parent == $trid)
			$budget += $rs->budget + __project_get_planbudget($activity, $rs->trid);
	}
	return $budget;
}
?>