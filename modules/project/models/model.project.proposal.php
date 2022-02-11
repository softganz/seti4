<?php
/**
* Project Model :: Proposal Model
* Created 2021-09-24
* Modify 	2021-09-24
*
* @param Array $args
* @return Object
*
* @usage new ProjectProposalModel([])
* @usage ProjectProposalModel::function($conditions, $options)
*/

import('model:project.php');
import('model:org.php');
import('model:node.php');

class ProjectProposalModel extends ProjectModel {

	public static function get($projectId, $options = '{}') {
		$defaults = '{debug: false, initTemplate: false, data: "info,indicator,objective,activity,expense,bigdata"}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;
		$tagname = 'develop';

		if (empty($projectId)) return false;

		if ($options->initTemplate) R::Module('project.template', $self, $projectId);

		$stmt = 'SELECT
			  t.`tpid` `projectId`
			, d.`tpid` `proposalId`
			, p.`tpid` `followId`
			, t.*, t.`status` `topicStatus`
			,  u.`username`, u.`name` `ownerName`
			, r.`body`, r.`homepage`, r.`email` prid
			, ps.`title` projectSetName
			, d.*
			, (SELECT SUM(`num1`) FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "activity") `planBudget`
			, c.`name` categoryName
			, o.`name` `orgName`
			, o.`shortname` `orgShortName`
			, d.`tpid` `proposalId`
			, p.`tpid` `followId`
			FROM %project_dev% d
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %topic_revisions% r USING (`revid`)
				LEFT JOIN %users% u ON u.`uid` = t.`uid`
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
				LEFT JOIN %project% p ON p.`tpid` = d.`tpid`
				LEFT JOIN %topic% ps ON ps.`tpid` = t.`parent`
				LEFT JOIN %tag% c ON c.`taggroup` = "project:category" AND c.`catid` = d.`category`
			WHERE t.`tpid` = :tpid LIMIT 1';
		$rs = mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname);

		if ($rs->_empty) return false;

		if (!$debug) mydb::clearprop($rs);

		$result = (Object) [
			'projectId' => $rs->projectId,
			'orgId' => $rs->orgid,
			'title' => $rs->title,
			'proposalId' => $rs->proposalId,
			'parentId' => $rs->parent,
			'followId' => $rs->followId,
			'tpid' => $rs->projectId,
			'parent' => $rs->parent,
			'orgid' => $rs->orgid,
			'submodule' => 'proposal',
			'parentTitle' => $rs->projectSetName,

			'uid' => $rs->uid,
			'RIGHT' => NULL,
			'RIGHTBIN' => NULL,
			'info' => mydb::clearprop($rs),
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
		/*
		if (user_access('administer projects')) $right=$right | _IS_ADMIN;
		if (user_access('edit own project content','edit own project content',$rs->uid)) $right=$right | _IS_OWNER;
		if (project_model::is_trainer_of($projectId)) $right=$right | _IS_TRAINER;
		if (in_array($result->info->status,[1,3]) && ($right & (_IS_ADMIN | _IS_TRAINER | _IS_OWNER))) $right=$right | _IS_EDITABLE;
		*/

		//$isEdit=(in_array($rs->status,[1,3]) || ($rs->status==2 && $cdate>='2014-04-19 16:00:00' && $cdate<='2014-04-27 16:00:00') ) && (user_access('administer projects','edit own project content',$rs->uid) || project_model::is_trainer_of($projectId));

		$result->info->orgMemberShipType = $orgMemberShip = NULL;



		// Get topic user of this topic and parent topic
		$membershipList = [];
		$topicUserDbs = mydb::select('SELECT u.*, t.`parent` FROM %topic_user% u LEFT JOIN %topic% t USING(`tpid`) WHERE u.`tpid` IN (:tpid, :parent) ORDER BY IF(`tpid` = :tpid, 1, 0) ASC, `uid`',':tpid',$projectId, ':parent', $result->info->parent);

		foreach ($topicUserDbs->items as $item) {
			$membershipList[$item->uid] = strtoupper($item->membership);
		}
		$result->info->membershipType = $membershipList[i()->uid];

		$result->info->membership = $membershipList;


		if (($result->info->orgid || $result->info->toorg) && i()->ok) {
			$result->info->orgMemberShipType = $orgMemberShip = OrgModel::officerType(SG\getFirst($result->info->orgid,$result->info->toorg), i()->uid);
		}

		$result->info->isOwner = i()->ok
			&& ($result->info->uid == i()->uid || $result->info->membershipType == 'OWNER');

		$result->info->isTrainer = (i()->ok && $result->info->membershipType == 'TRAINER')
			|| $orgMemberShip == 'TRAINER';

		$result->info->isOfficer = $orgMemberShip != '' ? $orgMemberShip : false;

		$result->info->isAdmin = user_access('administer projects')
			|| (i()->ok && (
				in_array($result->info->membershipType, ['ADMIN','MANAGER'])
				|| in_array($orgMemberShip, ['ADMIN','MANAGER'])
			));

		$result->info->isAccess = true;

		$result->info->isRight = user_access('administer projects', 'edit own project content', $result->info->uid)
			|| $result->info->isOwner
			|| $result->info->isTrainer
			|| in_array($orgMemberShip, ['ADMIN']);

		$result->info->isEdit = in_array($rs->status, [1,3]) && $result->info->isRight;
		$result->info->isEditDetail = $result->info->isEdit && $result->info->flag != _LOCKDETAIL;

		if ($result->info->isAdmin) $right = $right | _IS_ADMIN;
		if ($result->info->isOwner) $right = $right | _IS_OWNER;
		if ($result->info->isTrainer) $right = $right | _IS_TRAINER;
		if ($result->info->isAccess) $right = $right | _IS_ACCESS;
		if ($result->info->isRight) $right = $right | _IS_RIGHT;
		if ($result->info->isEdit) $right = $right | _IS_EDITABLE;
		if ($result->info->isEditDetail) $right = $right | _IS_EDITDETAIL;
		if (i()->ok) $right = $right | _IS_SIGNED;

		$result->RIGHT = $result->info->RIGHT = $right;
		$result->RIGHTBIN = $result->info->RIGHTBIN = decbin($right);

		$result->is->showBudget = $result->info->isAdmin
			|| $result->settings->budget->show == 'public'
			|| ($result->settings->budget->show == 'member' AND i()->ok)
			|| ($result->settings->budget->show == 'owner' AND $result->info->membershipType == 'OWNER')
			|| ($result->settings->budget->show == 'team' AND $result->info->membershipType)
			|| ($result->settings->budget->show == 'admin' AND $result->info->isAdmin)
			|| ($result->settings->budget->show == 'org' AND $result->info->orgMemberShipType);


		if ($options->data == 'info')
			return $result;

		$stmt='SELECT
			  p.*
			, c.`provname` `changwatName`
			, d.`distname` `ampurName`
			, s.`subdistname` `tambonName`
			FROM %project_prov% p
				LEFT JOIN %co_province% c ON c.`provid`=p.`changwat`
				LEFT JOIN %co_district% d ON d.`distid`=CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% s ON s.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
			WHERE `tpid`=:tpid AND `tagname` = :tagname';
		$dbs = mydb::select($stmt,':tpid',$projectId, ':tagname', _PROPOSAL_TAGNAME);
		$result->area = $dbs->items;


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
			WHERE `tpid` = :tpid AND `formid` = "develop" AND `part` = "problem"
			ORDER BY `trid` ASC;
			-- {key:"trid"}';
		$dbs = mydb::select($stmt, ':tpid', $projectId);
		$result->problem = $dbs->items;

		// Get Objective
		$stmt = 'SELECT
			  o.`tpid`
			, o.`trid`
			, o.`refid`
			, o.`tagname`
			, o.`parent` objectiveType
			, ot.`name` `objectiveTypeName`
			, o.`text1` `title`
			, o.`text2` `indicatorDetail`
			, IFNULL(o.`num1`,pb.`num1`) `problemsize`
			, o.`num2` `targetsize`
			, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
		FROM %project_tr% o
			LEFT JOIN %tag% ot ON ot.`taggroup` = "project:objtype" AND ot.`catid`=o.`parent`
			LEFT JOIN %project_tr% pb ON pb.`tpid` = o.`tpid` AND pb.`formid` = "develop" AND pb.`part` = "problem" AND pb.`tagname` = o.`tagname` AND pb.`refid` = o.`refid`
		WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "objective"
		ORDER BY o.`trid` ASC';
		$dbs = mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname);

		foreach ($dbs->items as $rs) {
			$rs->indicator = [];
			$result->objective[$rs->trid] = $rs;
		}

		// Get Objective Indicator
		$result->indicator = [];
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
			ORDER BY `objectiveId`, o.`tagname`, o.`trid`';
		$dbs = mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname);

		foreach ($dbs->items as $rs) {
			$result->objective[$rs->objectiveId]->indicator[$rs->tagname][] = $rs->indicatorId;
			$result->indicator[$rs->indicatorId] = $rs;
		}

		// Get Target
		$result->target = [];
		$stmt = 'SELECT
				  tg.*
			FROM %project_target% tg
			WHERE tg.`tpid` = :tpid AND tg.`tagname` = :tagname
			ORDER BY tg.`tgtid`';
		$dbs = mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname);

		foreach ($dbs->items as $rs) {
			$result->target[$rs->tgtid] = $rs;
		}

		// Get Guideline
		/*
		$stmt='SELECT * FROM
				(SELECT
				  o.`tpid`
				, o.`trid`
				, o.`refid`
				, o.`text1` `title`
				, o.`text2` `action`
				, tg.`catid`
				, tg.`description`
				, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
			FROM %project_tr% o
				LEFT JOIN %tag% tg ON tg.`taggroup`=:taggroup AND tg.`catid`=o.`refid`
			WHERE o.`tpid`=:tpid AND o.`formid`=:tagname AND o.`part`="guideline") a
			UNION ALL
			SELECT * FROM
				(SELECT
				  NULL `tpid`
				, NULL `trid`
				, `catid` `refid`
				, `name` `title`
				, NULL `action`
				, `catid`
				, `description`
				, NULL `uid`, NULL `created`, NULL `modified`, NULL `modifyby`
				FROM %tag% tg
					LEFT JOIN %project_tr% tr
						ON tr.`tpid` = :tpid AND tr.`formid` = :tagname
						AND tr.`part` = "guideline" AND tr.`refid` = tg.`catid`
				WHERE tg.`taggroup`=:taggroup AND tr.`trid` IS NULL) b
			ORDER BY IF(`refid` IS NULL,`trid`,`refid`) ASC
				';
		$dbs=mydb::select($stmt,':tpid', $projectId, ':tagname',$tagname, ':taggroup','project:guideline:'.$result->info->planGroup);
		$result->guideline=$dbs->items;
		foreach ($result->guideline as $key => $value) {
			if (empty($value->action) && $value->description) {
				$detail=json_decode($value->description);
				//$ret.=print_o($detail,'$detail');
				$detail->process=str_replace('<br />',"\n",$detail->process);
				$result->guideline[$key]->action=$detail->process;
			}
		}
		$result->guidelineQuery='<pre>'.mydb()->_query.'</pre>';
		*/

		// Get Plan Activity
		$result->activity = [];
		$stmt = 'SELECT
			  a.`tpid`
			, a.`trid`
			, a.`uid`
			, a.`sorder`
			, a.`parent`
			, a.`detail1` `title`
			, parent.`text1` `parentTitle`
			, a.`flag`
			, a.`num1` `budget`
			, a.`num2` `target`
			, a.`text1` `desc`
			, a.`text2` `indicator`
			, a.`detail2` `timeprocess`
			, a.`date1` `fromdate`
			, a.`date2` `todate`
			, a.`text3` `output`
			, a.`text6` `outcome`
			, a.`text7` `otherresource`
			, a.`text4` `copartner`
			, a.`text5` `budgetdetail`
			, a.`detail3` `targetOtherDesc`
			, 0 `totalActitity`
			, 0 `totalBudget`
			, 0 `totalExpense`
			, a.`created`
			, a.`modified`
			, a.`modifyby`

			, IFNULL(GROUP_CONCAT(po.`parent`),a.`parent`) `objectiveId`
			, GROUP_CONCAT(CONCAT(po.`trid`,"=",po.`parent`) SEPARATOR "|") `objectiveList`
			, GROUP_CONCAT(CONCAT(po.`parent`,"=",IFNULL(pot.`text1`,"")) SEPARATOR "|") `objectiveText`
			, NULL `parentObjectiveId`
			, NULL `parentObjectiveList`
			, NULL `parentObjectiveText`
			, 0 `childsCount`
			, NULL `childs`
		FROM %project_tr% a
			LEFT JOIN %project_tr% parent ON parent.`trid` = a.`parent`
			LEFT JOIN %project_tr% po ON po.`tpid` = a.`tpid` AND po.`refid` = a.`trid` AND po.`formid` = :tagname AND po.`part` = "actobj"
			LEFT JOIN %project_tr% pot ON pot.`trid` = po.`parent`
		WHERE a.`tpid` = :tpid AND a.`formid` = :tagname AND a.`part` = "activity"
		GROUP BY a.`trid`
		ORDER BY
			IF(a.`parent` IS NULL,0,1)
			, a.`sorder` ASC, a.`parent` ASC';
		$dbs = mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname);

		foreach ($dbs->items as $rs) {
			$rs->planBudget = 0;
			$rs->expense = [];
			$result->activity[$rs->trid] = $rs;
		}

		foreach ($dbs->items as $rs) {
			$result->activity[$rs->trid]->planBudget = ProjectProposalModel::planBudget($result->activity,$rs->trid);
			if ($rs->parent) {
				$result->activity[$rs->parent]->childsCount++;
				$result->activity[$rs->parent]->childs .= ($result->activity[$rs->parent]->childs?',':'').$rs->trid;
				$result->activity[$rs->parent]->childsCount++;
				$result->activity[$rs->parent]->childs .= ($result->activity[$rs->parent]->childs?',':'').$rs->trid;
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
		$dbs = mydb::select($stmt, ':tpid', $projectId, ':tagname', $tagname);
		foreach ($dbs->items as $item) {
			$result->expense[$item->trid] = $item;
			if (!isset($result->activity[$item->parent])) $result->activity[$item->parent] = (Object) [];
			$result->activity[$item->parent]->expense[] = $item->trid;
		}


		$data = [];
		$stmt = 'SELECT `fldname`,`flddata` FROM %bigdata% WHERE `keyid` = :tpid AND `keyname` = "project.develop" ORDER BY `fldname` ASC';
		$dbs = mydb::select($stmt, ':tpid', $projectId);
		foreach ($dbs->items as $item) {
			$result->data[$item->fldname] = $item->flddata;
		}

		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false, includeChildOrg: false, start: 0, items: 50, order: "d.`tpid`", sort: "ASC", key: null, value: null}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) ['count' => 0, 'items' => []];

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		if ($debug) {debugMsg($conditions, '$conditions'); debugMsg($options, '$options');}

		if ($conditions->userId == 'member') {
			mydb::where('(t.`uid` = :userId OR tu.`uid` = :userId)', ':userId', i()->uid);
		} else if ($conditions->userId) {
			mydb::where('t.`uid` = :userId', ':userId', $conditions->userId);
		}

		if ($conditions->childOf) {
			mydb::where('t.`parent` IN ( :parent )', ':parent', 'SET:'.$conditions->childOf);
		}

		if ($conditions->orgId) mydb::where('(t.`orgid` = :orgId'.($options->includeChildOrg ? ' || o.`parent` = :orgId' : '').')', ':orgId', $conditions->orgId);

		if ($conditions->childOfOrg) {
			mydb::where('o.`parent` IN ( :orgParent )', ':orgParent', 'SET:'.$conditions->childOfOrg);
		}

		if ($conditions->status) {
			mydb::where('d.`status` IN ( :status )', ':status', 'SET:'.$conditions->status);
		}

		// 'กำลังดำเนินโครงการ','ดำเนินการเสร็จสิ้น','ยุติโครงการ','ระงับโครงการ'
		// if ($conditions->status == 'process') {
		// 	mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ")');
		// } else if ($conditions->status == 'done') {
		// 	mydb::where('p.`project_status` IN ("ดำเนินการเสร็จสิ้น")');
		// } else if ($conditions->status == 'block') {
		// 	mydb::where('p.`project_status` IN ("ระงับโครงการ")');
		// } else if ($conditions->status == 'stop') {
		// 	mydb::where('p.`project_status` IN ("ยุติโครงการ")');
		// } else if ($conditions->status == 'all') {
		// } else {
		// 	mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ", "ดำเนินการเสร็จสิ้น")');
		// }

		if ($conditions->changwat) {
			mydb::where('t.`areacode` LIKE :changwat', ':changwat', $conditions->changwat.'%');
		}

		if ($conditions->budgetYear) {
			mydb::where('d.`pryear` = :budgetYear', ':budgetYear', $conditions->budgetYear);
		}

		if ($conditions->ownerType) {
			mydb::where('d.`ownertype` IN ( :ownerType )', ':ownerType', 'SET-STRING:'.$conditions->ownerType);
		}

		if ($conditions->title) {
			//mydb::where('(t.`title` LIKE :title)', ':title', '%'.$conditions->title.'%');
			$q = preg_replace('/\s+/', ' ', $conditions->title);
			if (preg_match('/^code:(\w.*)/', $q, $out)) {
				mydb::where('d.`tpid` = :tpid', ':tpid', $out[1]);
			} else {
				$searchList = explode('+', $q);
				//debugMsg('$q = '.$q);
				//debugMsg($searchList, '$searchList');
				$qLists = [];
				foreach ($searchList as $key => $str) {
					$str = trim($str);
					if ($str == '') continue;
					$qLists[] = '(t.`title` RLIKE :q'.$key.')';

					//$str=mysqli_real_escape_string($str);
					$str = preg_replace('/([.*?+\[\]{}^$|(\)])/','\\\\\1',$str);
					$str = preg_replace('/(\\\[.*?+\[\]{}^$|(\)\\\])/','\\\\\1',$str);

					// this comment for correct sublimetext syntax highlight
					// $str=preg_replace('/(\\[.*?+\[\]{}^$|(\)\\])/','\\\\\1',$str);

					// Replace space and comma with OR condition
					mydb::where(NULL, ':q'.$key, str_replace([' ',','], '|', $str));
				}
				if ($qLists) mydb::where('('.(is_numeric($q) ? 'd.`tpid` = :q OR ' : '').implode(' AND ', $qLists).')', ':q', $q);
			}

		} else if ($conditions->search) {
			mydb::where('(t.`title` LIKE :title OR d.`agrno` LIKE :title OR d.`prid` LIKE :title)', ':title', '%'.$conditions->search.'%');
		}

		mydb::value('$ORDER$', 'ORDER BY '.$options->order.' '.$options->sort);
		mydb::value('$LIMIT$', $options->items == '*' ? '' : 'LIMIT '.$options->sta.' '.$options->items);

		$stmt = 'SELECT
			d.`tpid` `projectId`
			, t.`title`
			, t.`orgid` `orgId`
			, t.`uid` `userId`
			, d.*
			, parent.`title` `parentTitle`
			, t.`areacode`
			, CONCAT(X(d.`location`), ",", Y(d.`location`)) `location`
			, o.`name` `orgName`
			, u.`username`, u.`name` `ownerName`
			, GROUP_CONCAT(tu.`uid`) `topicUsers`
			, (SELECT COUNT(*) FROM %topic% t WHERE t.`parent` = d.`tpid`) `childCount`
			, DATE_FORMAT(t.`created`, "%Y-%m-%d %H:%i:%s") `created`
			FROM %project_dev% d
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %users% u ON u.`uid` = t.`uid`
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
				LEFT JOIN %topic% parent ON parent.`tpid` = t.`parent`
				LEFT JOIN %topic_user% tu ON tu.`tpid` = d.`tpid`
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

		$result->items = mydb::select($stmt)->items;
		$result->count = count($result->items);

		if ($debug) debugMsg(mydb()->_query);

		return $result;
	}

	public static function create($data, $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'tpid' => NULL,
			'error' => false,
			'data' => NULL,
			'querys' => [],
		];

		$tpid = false;

		// Create member first project
		if ($data->title) {
			$data->projectset = $data->projectset == 'top' ? NULL : $data->projectset;

			$topic = (Object) [
				'tpid' => NULL,
				'revid' => NULL,
				'type' => 'project-develop',
				'parent' => $data->projectset,
				'status' => _DRAFT,
				'orgId' => SG\getFirst($data->orgId, $data->orgid),
				'uid' => i()->uid,
				'thread' => SG\getFirst($data->thread),
				'title' => $data->title,
				'areacode' => $data->areacode,
				'created' => SG\getFirst($data->created, date('Y-m-d H:i:s')),
				'timestamp' => date('Y-m-d H:i:s'),
				'ip' => ip2long(GetEnv('REMOTE_ADDR')),
			];

			if (empty($topic->orgId) && $topic->parent) {
				$parentInfo = mydb::select('SELECT `orgId` FROM %topic% WHERE `tpid` = :parent LIMIT 1', ':parent', $topic->parent);
				$topic->orgId = $parentInfo->orgId;
			}

			$stmt = 'INSERT INTO %topic%
				(
				  `type`,`status`,`orgId`,`uid`, `parent`, `thread`
				, `title`
				, `areacode`,`created`,`ip`
				)
				VALUES
				(
				  :type, :status, :orgId, :uid, :parent, :thread
				, :title
				, :areacode, :created, :ip
				)';
			mydb::query($stmt,$topic);
			$result->querys[]=mydb()->_query;

			if (mydb()->_error) {
				$result->error = mydb()->error;
			}

			$tpid = $topic->tpid = mydb()->insert_id;
			$result->tpid = $tpid;

			// Create topic_revisions
			$stmt='INSERT INTO %topic_revisions% (`tpid`,`uid`,`title`,`timestamp`) VALUES (:tpid,:uid,:title,:timestamp)';
			mydb::query($stmt,$topic);
			$result->querys[]=mydb()->_query;

			// Update revid to topic
			$revid=$topic->revid=mydb()->insert_id;
			mydb::query('UPDATE %topic% SET `revid`=:revid WHERE `tpid`=:tpid LIMIT 1',$topic);
			$result->querys[]=mydb()->_query;

			// Create topic_user
			if ($topic->parent) {
				$parentMemberShip = strtoupper( mydb::select('SELECT `membership` FROM %topic_user% WHERE `tpid` = :tpid AND `uid` = :uid LIMIT 1', ':tpid', $topic->parent, ':uid', $topic->uid)->membership);
				$result->querys[]=mydb()->_error.mydb()->_query;
			}

			$memberShipType = SG\getFirst($parentMemberShip,'OWNER');
			mydb::query('INSERT INTO %topic_user% (`tpid`, `uid`, `membership`) VALUES (:tpid, :uid, :membership)', $topic, ':membership', $memberShipType);
			$result->querys[]=mydb()->_query;

			// Create project
			$project = (Object) [
				'tpid' => $tpid,
				'prtype' => $data->prtype ? $data->prtype : 'โครงการ',
				'projectset' => $data->projectset,
				'pryear' => NULL,
				'date_approve' => empty($data->date_approve) ? NULL : $data->date_approve,
				'budget' => empty($data->budget) ? 0 : sg_strip_money($data->budget),
				'changwat' => empty($data->changwat) ? NULL : $data->changwat,
				'ampur' => empty($data->ampur) ? NULL : $data->ampur,
				'tambon' => empty($data->tambon) ? NULL : $data->tambon,
				'tagname' => _PROPOSAL_TAGNAME,
			];

			if (!empty($data->pryear)) $project->pryear = $data->pryear;
			else if ($project->date_approve) $project->pryear = sg_date($project->date_approve,'Y');
			else $project->pryear = date('Y');

			$stmt = 'INSERT INTO %project_dev%
				(
				  `tpid`
				, `pryear`
				, `budget`
				, `changwat`
				, `ampur`
				, `tambon`
				, `date_approve`
				)
				VALUES
				(
				  :tpid
				, :pryear
				, :budget
				, :changwat
				, :ampur
				, :tambon
				, :date_approve
				)';

			mydb::query($stmt, $project);

			$result->querys[] = mydb()->_query;

			if ($project->changwat) {
				$stmt = 'INSERT INTO %project_prov%
					(`tpid`, `tagname`, `tambon`, `ampur`, `changwat`)
					VALUES
					(:tpid, :tagname, :tambon, :ampur, :changwat)';

				mydb::query($stmt, $project);
				$result->querys[]=mydb()->_query;
			}

			// Trick firebase update
			$firebase = new Firebase('sg-project-man','update');
			$dataFirebase = [
				'tpid' => $tpid,
				'tags' => 'Project Proposal Create',
				'value' => $topic->title,
				'orgid' => SG\getFirst($topic->orgId,''),
				'url' => _DOMAIN.url('project/develop/'.$tpid),
				'time' => ['.sv'=>'timestamp'],
			];
			$firebase->post($dataFirebase);

			$result->dataFirebase = $dataFirebase;
		}

		$result->data = $data;

		$result->onProjectCreate = R::On('project.develop.create', $result);

		if ($debug) {
			debugMsg($data,'$data');
			debugMsg($topic,'$topic');
			debugMsg($project,'$project');
			debugMsg($result->querys,'$result->querys');
		}
		return $result;
	}

	public static function delete($projectId, $options = '{}') {
		$result = (Object) [
			'process' => [],
		];

		$proposalInfo = ProjectProposalModel::get($projectId);

		if ($proposalInfo->followId) {
			// Have project
			// Do nothing
		} else {
			// If no project follow
			$result = NodeModel::delete($projectId);

			mydb::query(
				'DELETE FROM %reaction% WHERE `action` LIKE "PDEV.%" AND `refid` = :projectId',
				[':projectId' => $projectId]
			);
			$result->process[] = mydb()->_query;

			mydb::query(
				'DELETE FROM %project_prov% WHERE `tpid` = :projectId AND `tagname` = "develop"',
				[':projectId' => $projectId]
			);
			$result->process[] = mydb()->_query;
		}

		mydb::query(
			'DELETE FROM %project_dev% WHERE `tpid` = :projectId LIMIT 1',
			[':projectId' => $projectId]
		);
		$result->process[] = mydb()->_query;

		mydb::query(
			'DELETE FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = "develop"',
			[':projectId' => $projectId]
		);
		$result->process[] = mydb()->_query;

		mydb::query(
			'DELETE FROM %project_target% WHERE `tpid` = :projectId AND `tagname` = "develop"',
			[':projectId' => $projectId]
		);
		$result->process[] = mydb()->_query;

		mydb::query(
			'DELETE FROM %bigdata% WHERE `keyname` = "project.develop" AND `keyid` = :projectId',
			[':projectId' => $projectId]
		);
		$result->process[] = mydb()->_query;

		mydb::query(
			'UPDATE %project_tr% SET `refid` = NULL WHERE `formid` = "info" AND `part` = "project" AND `refid` = :projectId ',
			[':projectId' => $projectId]
		);
		$result->process[] = mydb()->_query;

		R::model(
			'watchdog.log',
			'project',
			'Proposal Delete',
			'Proposal id '.$projectId.' - '.$proposalInfo->title.' was removed by '.i()->name.'('.i()->uid.')'
		);

		return $result;
	}

	public static function planBudget($activity, $trid) {
		$budget = 0;
		foreach ($activity as $rs) {
			if ($rs->parent == $trid) {
				$budget += $rs->budget + ProjectProposalModel::planBudget($activity, $rs->trid);
			}
		}
		return $budget;
	}

	public static function calculateBudget($projectId) {
		$debug = false;

		if (!$projectId) return fasle;

		// Update project develop budget
		$stmt = 'UPDATE %project_dev% d
			SET d.`budget` = (SELECT SUM(`num1`) FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "activity")
			WHERE `tpid` = :tpid';

		mydb::query($stmt, ':tpid', $projectId, ':tagname', _PROPOSAL_TAGNAME);

		if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');

		$result = mydb::select('SELECT `budget` FROM %project_dev% WHERE `tpid` = :tpid LIMIT 1', ':tpid', $projectId)->budget;

		return $result;
	}

	public static function calculateExpense($projectId) {
		$debug = false;

		if (!$projectId) return fasle;

		// Update each total expense
		$stmt='UPDATE %project_tr%
						SET `num4`=`num1`*`num2`*`num3`
						WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="exptr"';
		mydb::query($stmt,':tpid',$projectId, ':tagname',$tagname);
		if ($debug) debugMsg(mydb()->_query);

		// Update each main activity budget
		$stmt='UPDATE %project_tr% a
						LEFT JOIN (
							SELECT `parent`, `formid`, `part`, SUM(`num4`) total
							FROM %project_tr%
							WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="exptr"
							GROUP BY `parent`
							) e ON e.`parent`=a.`trid`
						SET a.`num1`=e.`total`
						WHERE a.`tpid`=:tpid AND a.`formid`=:tagname AND a.`part`="activity" ';
		mydb::query($stmt,':tpid',$projectId, ':tagname', _PROPOSAL_TAGNAME);
		if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');

		ProjectProposalModel::calculateBudget($projectId);

		return $ret;
	}

	public static function makeFollow($projectId, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$proposalInfo = ProjectProposalModel::get($projectId);
		$projectId = $proposalInfo->projectId;

		$uid = i()->uid;
		$owner = $proposalInfo->uid;
		$tagname = 'develop';

		// debugMsg($proposalInfo, '$proposalInfo');

		$result = (Object) [
			'extend' => NULL,
			'message' => [
				'<b>CREATE PROJECT FROM DEVELOPMENT</b>',
				'<b>CREATE PROJECT INFORMATION</b>',
			],
		];

		if (!$projectId) {
			$result->message[] = 'ERROR: NO PROPOSAL';
			return $result;
		}

		mydb::query('INSERT IGNORE INTO %project%
			(
			`tpid`, `projectset`, `prtype`, `pryear`, `prid`, `budget`
			, `date_from`, `date_end`, `date_approve`
			, `prtrainer`
			)
			SELECT
			d.`tpid`, t.`parent`, "โครงการ", `pryear`, `prid`, `budget`
			, d.`date_from`, d.`date_end`, CURDATE()
			, (SELECT GROUP_CONCAT(`name`)
					FROM %topic_user% tu
					LEFT JOIN %users% u USING(`uid`)
					WHERE `tpid` = :projectId AND `membership` = "TRAINER"
					GROUP BY `tpid`)
			FROM %project_dev% d
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE `tpid` = :projectId LIMIT 1',
			[':projectId' => $projectId]
		);

		$result->message[] = mydb()->_query;



		$proposalData = $proposalInfo->data;;


		// Add other information into project
		$result->message[] = '<b>UPDATE PROJECT INFORMATION</b>';

		$project = [
			'projectId' => $projectId,
			'prowner' => trim($proposalData['owner-prename'] . ' ' . $proposalData['owner-name'] . ' ' . $proposalData['owner-lastname']),
			'prphone' => '',
			'prteam' => '',
			'target' => '',
			'totaltarget' => '',
			'area' => SG\getFirst($proposalData['project-commune']),
		];

		$prphone = [];
		$prteam = [];


		if ($proposalData['owner-phone']) $prphone[] = $proposalData['owner-phone'];
		if ($proposalData['owner-mobile']) $prphone[] = $proposalData['owner-mobile'];
		$project['prphone'] = implode(',', $prphone);

		if ($proposalData['coowner-1-name']) $prteam[] = trim($proposalData['coowner-1-prename'] . ' ' . $proposalData['coowner-1-name'] . ' ' . $proposalData['coowner-1-lastname']);
		if ($proposalData['coowner-2-name']) $prteam[] = trim($proposalData['coowner-2-prename'] . ' ' . $proposalData['coowner-2-name'] . ' ' . $proposalData['coowner-2-lastname']);
		if ($proposalData['coowner-3-name']) $prteam[] = trim($proposalData['coowner-3-prename'] . ' ' . $proposalData['coowner-3-name'] . ' ' . $proposalData['coowner-3-lastname']);
		if ($proposalData['coowner-4-name']) $prteam[] = trim($proposalData['coowner-4-prename'] . ' ' . $proposalData['coowner-4-name'] . ' ' . $proposalData['coowner-4-lastname']);
		if ($proposalData['coowner-5-name']) $prteam[] = trim($proposalData['coowner-5-prename'] . ' ' . $proposalData['coowner-5-name'] . ' ' . $proposalData['coowner-5-lastname']);
		$project['prteam'] = implode(' , ', $prteam);

		$project['target'] = 'กลุ่มเป้าหมายหลัก'._NL._NL
			.$proposalData['project-target']._NL._NL
			.(trim($proposalData['target-secondary-detail']) != '' ? 'กลุ่มเป้าหมายรอง'._NL._NL . $proposalData['target-secondary-detail'] : '');
		$project['totaltarget'] = $proposalData['target-main-total'] + $proposalData['target-secondary-total'];


		$stmt = 'UPDATE %project% SET
			`prowner` = :prowner, `prphone` = :prphone, `prteam` = :prteam
			, `target` = :target, `totaltarget` = :totaltarget
			, `area` = :area
			WHERE `tpid` = :projectId LIMIT 1';

		mydb::query($stmt, $project);
		$result->message[] = mydb()->_query;



		// TODO: Update orgnamedo when not LocalFund
		// FOR LOCAL FUND ONLY
		if (mydb::columns('project','orgnamedo')) {
			$project['orgnamedo'] = $proposalInfo->info->orgnamedo;
			$project['supporttype'] = $proposalInfo->info->category;
			$project['supportorg'] = $proposalInfo->info->ownergroup;

			$stmt = 'UPDATE %project% SET
				  `orgnamedo` = :orgnamedo
				, `supporttype` = :supporttype
				, `supportorg` = :supportorg
				WHERE `tpid` = :projectId LIMIT 1';

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


		// ADD MAIN ACTIVITY BY TARGET TO TOPIC PARENT
		// กิจกรรมหลักตามกลุ่มเป้าหมายหลัก to topic parent
		$result->message[] = '<b>ADD MAIN ACTIVITY BY TARGET TO TOPIC PARENT</b>';
		foreach ($proposalInfo->data as $key => $value) {
			if (preg_match('/^act\-target\-/', $key)) {
				$data = ['projectId' => $projectId];
				list($a, $b, $data['tgtid'], $data['parent']) = explode('-',$key);
				if (empty($value) || $data['tgtid'] == 'other') continue;
				$stmt = 'INSERT IGNORE INTO %topic_parent% (`tpid`, `parent`, `tgtid`) VALUES (:projectId, :parent, :tgtid)';
				mydb::query($stmt, $data);
				$result->message[] = mydb()->_query;
			}
		}



		$result->message[] = '<b>ADD BASIC INFORMATION TO PROJECT_TR</b>';
		$stmt = 'INSERT INTO %project_tr%
			(`tpid`, `formid`, `part`, `uid`, `text1`, `text5`, `created`)
			VALUES
			(:projectId, "info", "basic", :uid, :text1, :text5, :created)';
		mydb::query($stmt, ':projectId', $projectId, ':uid', $uid, ':text1', $proposalInfo->data['project-problem'], ':text5', $proposalInfo->data['conversion-human'], ':created', date('U'));
		$result->message[] = mydb()->_query;




		// Set topic type to project and locked topic
		$result->message[] = '<b>UPDATE TOPIC TYPE TO PROJECT</b>';
		$projectStatus = _LOCK; // or _LOCKDETAIL
		mydb::query('UPDATE %topic% SET `type` = "project", `status` = :status WHERE `tpid` = :projectId LIMIT 1', ':projectId', $projectId, ':status', $projectStatus);
		$result->message[] = mydb()->_query;




		// Add Owner into Topic Creater
		$result->message[] = '<b>CREATE TOPIC USER (topic_user)</b>';
		mydb::query('INSERT IGNORE INTO %topic_user% ( `tpid`,`uid`,`membership` ) SELECT `tpid`,`uid`,"OWNER" FROM %topic% WHERE `tpid` = :projectId LIMIT 1', ':projectId', $projectId);
		$result->message[] = mydb()->_query;


		// Set topic type to project and locked topic
		$result->message[] = '<b>UPDATE DEV STATUS TO PASS</b>';
		$projectStatus = _LOCK; // or _LOCKDETAIL
		mydb::query('UPDATE %project_dev% SET `status` = 10 WHERE `tpid` = :projectId LIMIT 1', ':projectId', $projectId);
		$result->message[] = mydb()->_query;


		/*
		// Old version on ชุมชนน่าอยู่
		mydb::query('UPDATE %project_tr% SET `flag` = 1, `num2` = IFNULL(`num3`, 0) + IFNULL(`num4`,0) + IFNULL(`num5`, 0) + IFNULL(`num6`,0) WHERE `tpid` = :projectId AND `formid` = "info" AND `part` = "mainact"', ':projectId', $projectId);
		$result->message[] = mydb()->_query;
		*/


		// Add Project Bigdata from Develop Bigdata
		$result->message[] = '<b>CREATE BIGDATA INFORMATION (bigdata, keyname = project.develop => project.info)</b>';
			$stmt = 'INSERT INTO %bigdata%
				( `keyname`, `keyid`, `fldname`, `fldtype`, `flddata`, `created`, `ucreated`, `modified`, `umodified` )
				SELECT "project.info", `keyid`, `fldname`, `fldtype`, `flddata`, UNIX_TIMESTAMP(), :ucreated, NULL, NULL
					FROM %bigdata%
					WHERE `keyid` = :projectId AND `keyname` = "project.develop"
				';
			mydb::query($stmt, ':projectId', $projectId, ':ucreated', $owner);
			$result->message[] = mydb()->_query;




		// Add Project Province from Develop Province
		$result->message[] = '<b>CREATE PROJECT PROVINCE (project_prov , tagname = '.$tagname.' => info)</b>';
			$stmt = 'INSERT INTO %project_prov%
				( `tpid`, `tagname`, `house`, `village`, `tambon`, `ampur`, `changwat`, `areatype` )
				SELECT `tpid`, "info", `house`, `village`, `tambon`, `ampur`, `changwat`, `areatype`
					FROM %project_prov%
					WHERE `tpid` = :projectId AND `tagname` = :tagname
				';
			mydb::query($stmt, ':projectId', $projectId, ':tagname', $tagname);
			$result->message[] = mydb()->_query;


		// Add Project Target From Develop Target
		/*
		$result->message[] = '<h3>Create Target</h3>';
		$oldTarget=mydb::select('SELECT * FROM %project_target% WHERE `tpid`=:projectId AND `tagname`=:tagname ORDER BY `tgtid` ASC;',':projectId',$projectId, ':tagname',$tagname)->items;
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
			$devSupportPlan = mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:projectId AND `formid`=:tagname AND `part`="supportplan" ORDER BY `trid` ASC;', ':projectId', $projectId, ':tagname', $tagname)->items;
			$stmt = mydb::create_insert_cmd('project_tr', reset($devSupportPlan), ':');
			foreach ($devSupportPlan as $rs) {
				$newRs = [];
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
			if ($proposalInfo->info->targetgroup) {
				$data = [];
				$stmt = 'INSERT IGNORE INTO %project_target% (`tpid`, `tagname`, `tgtid`, `amount`) VALUES (:projectId, :tagname, :tgtid, :amount)';
				mydb::query($stmt, ':projectId', $projectId, ':tagname', 'info', ':tgtid', $proposalInfo->info->targetgroup, ':amount', $proposalInfo->data['target-main-total']);
				$result->message[] = mydb()->_query;
			}
		*/



		// CREATE PROJECT TARGET
		$result->message[] = '<b>CREATE PROJECT TARGET (project_target , tagname = '.$tagname.' => info</b>';
			$devTarget = mydb::select('SELECT * FROM %project_target% WHERE `tpid` = :projectId AND `tagname` LIKE :tagname;',':projectId', $projectId, ':tagname', $tagname.'%')->items;
			$stmt = mydb::create_insert_cmd('project_target', reset($devTarget), ':');

			foreach ($devTarget as $rs) {
				$newRs = [];
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
			$devProblem = mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:projectId AND `formid`=:tagname AND `part`="problem" ORDER BY `trid` ASC; -- {key:"trid"}',':projectId',$projectId, ':tagname',$tagname)->items;
			$stmt = mydb::create_insert_cmd('project_tr', reset($devProblem), ':');
			foreach ($devProblem as $rs) {
				$newRs = [];
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
			$oldObjective=mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:projectId AND `formid`=:tagname AND `part`="objective" ORDER BY `trid` ASC; -- {key:"trid"}',':projectId',$projectId, ':tagname',$tagname)->items;
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
			$oldIndicator = mydb::select('SELECT * FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = :tagname AND `part` = "indicator" ORDER BY `trid` ASC; -- {key:"trid"}', ':projectId', $projectId, ':tagname', $tagname)->items;
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
			$devActivity = mydb::select('SELECT * FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = :tagname AND `part` = "activity" ORDER BY `parent`,`sorder` ASC; -- {key:"trid"}', ':projectId', $projectId, ':tagname', $tagname)->items;
			foreach ($devActivity as $rs) {
				$activity = $proposalInfo->activity[$rs->trid];

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
				$activity = $proposalInfo->activity[$oldid];
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
				$activity = $proposalInfo->activity[$rs->trid];
				//$result->message[] = $activity;

				// แนวคิดเดิมคือสร้างปฏิทินเฉพาะกิจกรรมที่ระบุค่าใช้จ่ายเท่านั้น
				// แนวคิดใหม่ เอาเฉพาะที่กำหนด budget นำมาใส่ไว้ในปฏิทินทั้งหมด
				//if (empty($activity->budget)) continue;

				//$result->message[] = '<font color="red">Add calendar</font>'.$parent.$newActivityId[$parent];

				$calData = (Object) [];
				$calData->tpid = $projectId;
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
			$oldActobj=mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:projectId AND `formid`=:tagname AND `part`="actobj" ORDER BY `trid` ASC; -- {key:"trid"}',':projectId',$projectId, ':tagname',$tagname)->items;
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
			$oldExptr=mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:projectId AND `formid`=:tagname AND `part`="exptr" ORDER BY `trid` ASC; -- {key:"trid"}',':projectId',$projectId, ':tagname',$tagname)->items;
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

		$partList = 'studentPlan,owner,coorg';
		$result->message[] = '<b>COPY PROJECT TRAN of ('.$partList.')</b>';
			$proposalTran = mydb::select(
				'SELECT *
				FROM %project_tr%
				WHERE `tpid` = :projectId AND `formid` = :tagname AND `part` IN ( :partList )
				ORDER BY `trid` ASC;
				-- {key:"trid"}',
				[
					':projectId' => $projectId,
					':tagname' => $tagname,
					':partList' => 'SET-STRING:'.$partList,
				]
			)->items;
			foreach ($proposalTran as $rs) {
				$trid = $rs->trid;
				unset($rs->trid);
				$stmt = mydb::create_insert_cmd('project_tr', $rs, ':');
				$rs->formid = 'info';
				$newRs = [];
				foreach ($rs as $key => $value) {
					$newRs[$key.':'] = $value;
				}
				mydb::query($stmt,$newRs);
				// $result->message[] = '<p>'.$stmt.'</p>';
				$result->message[] = mydb()->_query;
			}

			// mydb::query(
			// 	'INSERT INTO %project_tr%
			// 	SELECT * FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = "develop" AND `part` IN ( :partList)',
			// 	[
			// 		':projectId' => $projectId,
			// 		':partList' => 'SET-STRING:'.$partList,
			// 	]
			// );
			// $result->message[] = mydb()->_query;

		/*
		$result->message[] = '<b>CREATE PROJECT ACTIVITY TARGET (project_target, tagname = '.$tagname.':mainact => project:mainact)</b>';
		$oldTarget=mydb::select('SELECT * FROM %project_target% WHERE `tpid`=:projectId AND `tagname`=:tagname ORDER BY `tgtid` ASC;',':projectId',$projectId, ':tagname',$tagname.':mainact')->items;
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
		$devActivity=mydb::select('SELECT * FROM %project_tr% WHERE `tpid`=:projectId AND `formid`=:tagname AND `part`="activity" ORDER BY `trid` ASC; -- {key:"trid"}',':projectId',$projectId, ':tagname',$tagname)->items;
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
		mydb::query('UPDATE %project% SET `budget` = :budget WHERE `tpid` = :projectId', ':projectId', $projectId, ':budget', $proposalInfo->info->budget);
		$result->message[] = mydb()->_query;

		$result->extend = R::Model('project.develop.follow.create.extend',$proposalInfo);




		$result->message[] = '<a href="'.url('project/'.$projectId).'" target="_blank">ติดตามโครงการ</a>';

		return $result;

	}
}
?>