<?php
/**
* Project Model :: Project Follow Model
* Created 2021-08-13
* Modify  2021-08-13
*
* @param Array $args
* @return Widget
*
* @usage new ProjectModel([])
*/

$debug = true;

class ProjectModel {

	/**
	* Organization Get
	*
	* @param Object $conditions
	* @return Object $options
	*/

	public static function get($projectId, $options = '{}') {
		import('model:org.php');

		$defaults = '{debug: false, initTemplate: false, type: NULL, data: "info,indicator,objective,activity,expense,bigdata"}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;
		$tagname = 'info';

		if (empty($projectId)) return false;

		$subModuleNameList = array('โครงการ'=>'default','ชุดโครงการ'=>'set','แผนงาน'=>'planning');

		if ($options->initTemplate) R::Module('project.template', $self, $projectId);

		if ($options->type == '*') {
			$stmt = 'SELECT
				  t.`tpid` `projectId`, t.`uid`, t.`orgid`, t.`parent`, t.`title`
				, u.`username`
				, u.`name` `ownerName`
				, prset.`title` `projectset_name`
				, prparent.`prtype` `projectParentType`
				, t.`status` `flag`
				, t.`type`
				, p.*
				, t.`tpid`
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
				WHERE t.`tpid` = :tpid LIMIT 1';
		} else {
			$stmt = 'SELECT
				  p.`tpid` `projectId`, t.`uid`, t.`orgid`, t.`parent`, t.`title`
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
					LEFT JOIN %project_dev% d ON d.`tpid` = p.`tpid`
					LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid` LEFT JOIN %db_org% op ON op.`orgid` = o.`parent`
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
		//debugMsg('<pre>'.$stmt.'</pre>');
		//debugMsg('<pre>tpid='.$projectId.'<br />'.mydb()->_query.'</pre>');

		if ($rs->_empty) return false;

		if (!$debug) mydb::clearprop($rs);

		//if ($rs->prtype == 'แผนงาน') $tagname = 'planning';

		$result = (Object) [
			'projectId' => $rs->projectId,
			'parentId' => $rs->parent,
			'orgId' => $rs->orgid,
			'title' => $rs->title,
			'tpid' => $rs->tpid,
			'orgid' => $rs->orgid,
			'uid' => $rs->uid,
			'submodule' => $subModuleNameList[$rs->prtype],
			'RIGHT' => 0,
			'RIGHTBIN' => NULL,
			'info' => $rs,
			'settings' => NULL,
			'membership' => NULL,
			'officers' => NULL,
			'right' => (Object) [],
			'is' => (Object) [],
		];

		$template = [];
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
			, a.`tagName`
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
			$result->activity[$rs->trid]->planBudget = ProjectModel::getPlanBudget($result->activity, $rs->trid);
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

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false, start: 0, items: 50, order: "p.`tpid`", sort: "ASC", key: null, value: null}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = new MyDbResult;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$id = $conditions;
			$conditions = (Object) ['id' => $id];
		}

		if ($debug) debugMsg($conditions, '$conditions');

		if ($conditions->projectType) {
			mydb::where('p.`prtype` IN ( :projectType)', ':projectType', 'SET-STRING:'.$conditions->projectType);
		} else {
			mydb::where('p.`prtype` = :projectType', ':projectType', 'โครงการ');
		}

		if ($conditions->userId == 'member') {
			mydb::where('(t.`uid` = :userId OR tu.`uid` = :userId)', ':userId', i()->uid);
		} else if ($conditions->userId) {
			mydb::where('t.`uid` = :userId', ':userId', $conditions->userId);
		}

		if ($conditions->childOf) {
			mydb::where('t.`parent` IN ( :parent )', ':parent', 'SET:'.$conditions->childOf);
		}

		if ($conditions->orgId) mydb::where('t.`orgid` = :orgId', ':orgId', $conditions->orgId);

		if ($conditions->childOfOrg) {
			mydb::where('o.`parent` IN ( :orgParent )', ':orgParent', 'SET:'.$conditions->childOfOrg);
		}

		// 'กำลังดำเนินโครงการ','ดำเนินการเสร็จสิ้น','ยุติโครงการ','ระงับโครงการ'
		if ($conditions->status == 'process') {
			mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ")');
		} else if ($conditions->status == 'done') {
			mydb::where('p.`project_status` IN ("ดำเนินการเสร็จสิ้น")');
		} else if ($conditions->status == 'block') {
			mydb::where('p.`project_status` IN ("ระงับโครงการ")');
		} else if ($conditions->status == 'stop') {
			mydb::where('p.`project_status` IN ("ยุติโครงการ")');
		} else if ($conditions->status == 'all') {
		} else {
			mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ", "ดำเนินการเสร็จสิ้น")');
		}

		if ($conditions->changwat) {
			mydb::where('t.`areacode` LIKE :changwat', ':changwat', $conditions->changwat.'%');
		}

		if ($conditions->budgetYear) {
			mydb::where('p.`pryear` = :budgetYear', ':budgetYear', $conditions->budgetYear);
		}

		if ($conditions->ownerType) {
			mydb::where('p.`ownertype` IN ( :ownerType )', ':ownerType', 'SET-STRING:'.$conditions->ownerType);
		}

		if ($conditions->title) {
			//mydb::where('(t.`title` LIKE :title)', ':title', '%'.$conditions->title.'%');
			$q = preg_replace('/\s+/', ' ', $conditions->title);
			if (preg_match('/^code:(\w.*)/', $q, $out)) {
				mydb::where('p.`tpid` = :tpid', ':tpid', $out[1]);
			} else {
				$searchList = explode('+', $q);
				//debugMsg('$q = '.$q);
				//debugMsg($searchList, '$searchList');
				$qLists = array();
				foreach ($searchList as $key => $str) {
					$str = trim($str);
					if ($str == '') continue;
					$qLists[] = '(t.`title` RLIKE :q'.$key.')';

					//$str=mysqli_real_escape_string($str);
					$str = preg_replace('/([.*?+\[\]{}^$|(\)])/','\\\\\1',$str);
					$str = preg_replace('/(\\\[.*?+\[\]{}^$|(\)\\\])/','\\\\\1',$str);

					// this comment for correct sublimetext syntax highlight
					// $str=preg_replace('/(\\[.*?+\[\]{}^$|(\)\\])/','\\\\\1',$str);

					// Replace space and comma with OR conditions
					mydb::where(NULL, ':q'.$key, str_replace([' ',','], '|', $str));
				}
				if ($qLists) mydb::where('('.(is_numeric($q) ? 'p.`tpid` = :q OR ' : '').implode(' AND ', $qLists).')', ':q', $q);
			}

		} else if ($conditions->search) {
			mydb::where('(t.`title` LIKE :title OR p.`agrno` LIKE :title OR p.`prid` LIKE :title)', ':title', '%'.$conditions->search.'%');
		}

		mydb::value('$ORDER$', 'ORDER BY '.$options->order.' '.$options->sort);
		mydb::value('$LIMIT$', $options->items == '*' ? '' : 'LIMIT '.$options->sta.' '.$options->items);

		$stmt = 'SELECT
			p.`tpid` `projectId`
			, t.`title`
			, t.`orgid` `orgId`
			, p.*
			, t.`areacode`
			, CONCAT(X(p.`location`), ",", Y(p.`location`)) `location`
			, o.`name` `orgName`
			, u.`username`, u.`name` `ownerName`
			, (SELECT COUNT(*) FROM %topic% t WHERE t.`parent` = p.`tpid`) `childCount`
			, DATE_FORMAT(t.`created`, "%Y-%m-%d %H:%i:%s") `created`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %users% u ON u.`uid` = t.`uid`
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
				LEFT JOIN %topic_user% tu USING(`tpid`)
			%WHERE%
			GROUP BY `projectId`
			$ORDER$
			$LIMIT$;
			-- '
			. json_encode(
				[
					'key' => $options->key,
					'value' => $options->value,
					'sum' => 'budget',
				]
			);

		$result = mydb::select($stmt)->items;

		if ($debug) debugMsg(mydb()->_query);

		return $result;
	}

	public static function delete($projectId, $options = '{}') {
		import('model:node.php');

		$defaults = '{debug:false, delete: "All"}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;


		$result = (Object) [
			'complete' => false,
			'error' => false,
			'process' => ['r_project_delete request'],
		];

		if (empty($projectId)) {
			$result->error = 'Empty topic';
			return $result;
		}

		$projectInfo = ProjectModel::get($projectId);

		$result = NodeModel::delete($projectId);

		$result->process[] = 'ProjectModel::delete() request';

		mydb::query('DELETE FROM %project% WHERE `tpid` = :tpid',':tpid',$projectId);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %project_dev% WHERE `tpid` = :tpid',':tpid',$projectId);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %project_tr% WHERE `tpid` = :tpid',':tpid',$projectId);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %project_tr_bak% WHERE `tpid` = :tpid',':tpid',$projectId);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE a FROM %project_activity% a LEFT JOIN %calendar% c ON c.`id`=a.`calid` WHERE c.`tpid` = :tpid',':tpid',$projectId);
		$result->process[]=mydb()->_query;

		if (mydb::table_exists('%project_actguide%')) {
			mydb::query('DELETE FROM %project_actguide% WHERE `tpid` = :tpid',':tpid',$projectId);
			$result->process[]=mydb()->_query;
		}

		if (mydb::table_exists('%project_prov%')) {
			mydb::query('DELETE FROM %project_prov% WHERE `tpid` = :tpid',':tpid',$projectId);
			$result->process[]=mydb()->_query;
		}

		mydb::query('DELETE p FROM %property% p LEFT JOIN %calendar% c ON p.`module`="calendar" AND c.`id`=p.`propid` WHERE c.`tpid` = :tpid',':tpid',$projectId);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %calendar% WHERE `tpid` = :tpid',':tpid',$projectId);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %property% WHERE `module` = "project" AND `propid`=:propid',':propid',$projectId);
		$result->process[]=mydb()->_query;

		if (mydb::table_exists('%project_gl%')) {
			mydb::query('DELETE FROM %project_gl% WHERE `tpid` = :tpid',':tpid',$projectId);
			$result->process[]=mydb()->_query;
		}

		if (mydb::table_exists('%project_target%')) {
			mydb::query('DELETE FROM %project_target% WHERE `tpid` = :tpid',':tpid',$projectId);
			$result->process[]=mydb()->_query;
		}

		if (mydb::table_exists('%project_paiddoc%')) {
			mydb::query('DELETE FROM %project_paiddoc% WHERE `tpid` = :tpid',':tpid',$projectId);
			$result->process[]=mydb()->_query;
		}

		mydb::query('DELETE FROM %bigdata% WHERE `keyname` LIKE "project.%" AND `keyid` = :tpid',':tpid',$projectId);
		$result->process[]=mydb()->_query;

		//die(print_o($result,'$result'));

		model::watch_log(
			'project',
			'Follow Delete',
			'Project id '.$projectId.' - '.$projectInfo->title.' was removed by '.i()->name.'('.i()->uid.')'
		);

		// delete was complete
		$result->complete = true;
		$result->process[] =  'ProjectModel::delete() complete';

		return $result;
	}

	// public static function getBasicInfo($projectId) {
	// 	return mydb::select(
	// 		'SELECT
	// 		`trid` `tranId`, `tpid` `projectId`
	// 		, `text1` `หลักการและเหตุผล`
	// 		, `text2` `วิธีการดำเนินกิจกรรม`
	// 		, `text3` `รายละเอียดกลุ่มเป้าหมาย`
	// 		, `text4` `ตัวชี้วัดกิจกรรม`
	// 		, `text5` `ผลการดำเนินงานที่คาดว่าจะได้รับ`
	// 		, `text6` `กรอบแนวคิด`
	// 		, `detail1` `ตำแหน่ง (เจ้าหน้าที่รับผิดชอบ)`
	// 		, `detail2` `เบอร์โทร (เจ้าหน้าที่รับผิดชอบ)`
	// 		, `detail3` `ชื่อ - สกุล (ผู้เสนอโครงการ)`
	// 		, `detail4` `ตำแหน่ง (ผู้เสนอโครงการ)`
	// 		-- , `detail5` `เบอร์โทร (ผู้เสนอโครงการ)`

	// 		-- formid = "info"
	// 		-- part = "basic"
	// 		-- refid = ความสอดคล้องกับแผนงาน/ประเด็น
	// 		-- detail1 = ภายใต้แผนงาน (ระบุชื่อแผนงาน)

	// 		FROM %project_tr%
	// 		WHERE `tpid` = :projectId AND `formid` = "info" AND `part` = "basic"
	// 		LIMIT 1',
	// 		[':projectId' => $projectId]
	// 	);
	// }

	public static function getPlanBudget($activity, $trid) {
		$budget = 0;
		foreach ($activity as $rs) {
			if ($rs->parent == $trid)
				$budget += $rs->budget + ProjectModel::getPlanBudget($activity, $rs->trid);
		}
		return $budget;
	}

	public static function getOrgCo($conditions, $options = '{}') {
		$defaults = '{debug: false, start: 0, items: 50, order: "CONVERT(`changwatName` USING tis620) ASC, CONVERT(`orgName` USING tis620)", sort: "ASC"}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$projectId = $conditions;
			$conditions = (Object) ['projectId' => $projectId];
		}

		if ($debug) debugMsg($conditions, '$conditions');

		$result = (Object) [
			'count' => 0,
			'items' => [],
		];

		mydb::where('co.`tpid` = :projectId', ':projectId' , $conditions->projectId);
		if ($conditions->zone) mydb::where('LEFT(o.`areacode`, 1) IN ( :zone )', ':zone', 'SET:'.cfg('zones')[$conditions->zone]['zoneid']);
		mydb::value('$ORDER$', $options->order, false);
		mydb::value('$SORT$', $options->sort);

		$result->items = mydb::select(
			'SELECT
			co.`tpid` `projectId`, co.`orgId`, o.`name` `orgName`
			, o.`shortname`
			, cop.`provname` `changwatName`
			, o.`location`
			, u.`name` `ownerName`
			, co.`created`
			FROM %project_orgco% co
				LEFT JOIN %db_org% o ON o.`orgId` = co.`orgId`
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`, 2)
				LEFT JOIN %users% u ON u.`uid` = co.`uid`
			%WHERE%
			ORDER BY $ORDER$ $SORT$'
		)->items;

		$result->count = is_array($result->items) ? count($result->items) : 0;
		// debugMsg(mydb()->_query);
		// debugMsg($result, '$result');
		return $result;
	}
}
?>