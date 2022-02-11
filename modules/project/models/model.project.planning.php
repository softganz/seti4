<?php
/**
* Model :: Description
* Created 2021-09-27
* Modify 	2021-09-27
*
* @param Array $args
* @return Object
*
* @usage new ProjectFollowModel([])
* @usage ProjectFollowModel::function($conditions, $options)
*/

$debug = true;

import('model:project.php');
import('model:org.php');

class ProjectPlanningModel extends ProjectModel {

	public static function get($planningId, $options = '{}') {
		$defaults = '{debug: false, initTemplate: false, data: "info,indicator,objective,activity,expense,bigdata"}';
		$options = sg_json_decode($options,$defaults);
		$debug = $options->debug;
		$tagname = 'info';

		if (empty($planningId)) return false;

		if ($options->initTemplate) R::Module('project.template', $self, $planningId);

		$stmt = 'SELECT
			p.`tpid` `projectId`
			, p.*
			, t.`title`
			, t.`orgid`
			, t.`uid`
			, t.`areacode`
			, o.`shortname`
			, pt.`refid` `planGroup`, pg.`name` `planName`
			, p.`project_status`+0 `project_statuscode`
			, tp.`title` `projectset_name`
			, u.`username`, u.`name` `ownerName`
			FROM %project% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				LEFT JOIN %users% u ON u.`uid` = t.`uid`
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
				LEFT JOIN %topic% tp ON tp.`tpid` = t.`parent`
				LEFT JOIN %project_tr% pt ON pt.`tpid` = p.`tpid` AND pt.`formid` = "info" AND pt.`part` = "title"
				LEFT JOIN %tag% pg ON pg.`taggroup` = "project:planning" AND pg.`catid` = pt.`refid`
			WHERE p.`prtype` = "แผนงาน" AND p.`tpid` = :tpid
			LIMIT 1';

		$rs = mydb::select($stmt,':tpid',$planningId);
		//debugMsg($rs,'$rs');

		if ($rs->_empty) return false;


		if (!$debug) mydb::clearprop($rs);

		$result = (Object) [
			'projectId' => $rs->projectId,
			'orgId' => $rs->orgid,
			'tpid' => $rs->tpid,
			'title' => $rs->title,
			'RIGHT' => NULL,
			'RIGHTBIN' => NULL,
			'info' => $rs,
		];

		$right = 0;

		$result->info->areaName = SG\implode_address($result->info);

		if ($result->info->date_from == '0000-00-00') $result->info->date_from = '';
		if ($result->info->date_end == '0000-00-00') $result->info->date_end = '';

		$result->info->lockReportDate=project_model::get_lock_report_date($planningId);


		foreach (mydb::select('SELECT * FROM %topic_user% WHERE `tpid` = :tpid',':tpid',$planningId)->items as $item) {
			$membershipList[$item->uid] = strtoupper($item->membership);
		}
		$result->info->membershipType = $membershipList[i()->uid];


		$orgMemberShip = NULL;
		if ($result->info->orgid && i()->ok)
			$orgMemberShip = OrgModel::officerType($result->info->orgid,i()->uid);

		$result->info->isOwner = i()->ok
			&& ($result->info->uid == i()->uid || $result->info->membershipType == 'OWNER');

		$result->info->isTrainer = (i()->ok && $result->info->membershipType == 'TRAINER')
			|| $orgMemberShip=='TRAINER';

		$result->info->isOfficer = $orgMemberShip != '' ? $orgMemberShip : false;
		$result->info->isAdmin = user_access('administer projects');
		$result->info->isAccess = true;
		$result->info->isRight = user_access('administer projects','edit own project content',$result->info->uid)
			|| $result->info->isOwner
			|| $result->info->isTrainer
			|| in_array($orgMemberShip,array('ADMIN'));

		$result->info->isEdit = false;
		$result->info->isEdit = $result->info->project_statuscode == 1 && $result->info->isRight;
		$result->info->isEditDetail = $result->info->isEdit && $result->info->flag != _LOCKDETAIL;

		if ($result->info->isAdmin) $right = $right | _IS_ADMIN;
		if ($result->info->isOwner) $right = $right | _IS_OWNER;
		if ($result->info->isTrainer) $right = $right | _IS_TRAINER;
		if ($result->info->isAccess) $right = $right | _IS_ACCESS;
		if ($result->info->isEdit) $right = $right | _IS_EDITABLE;
		if ($result->info->isEditDetail) $right = $right | _IS_EDITDETAIL;


		$result->RIGHT = $result->info->RIGHT = $right;
		$result->RIGHTBIN = $result->info->RIGHTBIN = decbin($right);

		$result->membership = $membershipList;

		if ($options->data == 'info') return $result;



		// Get Other Information

		// Get Basic Info
		$stmt='SELECT
				o.`tpid`, o.`trid`
				, o.`text1` `situation`
				, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
			FROM %project_tr% o
			WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "basic"
			LIMIT 1';
		$result->basic = mydb::clearprop(mydb::select($stmt,':tpid', $planningId, ':tagname',$tagname));

		// Get Problem
		$stmt='SELECT
			a.*
			-- , tg.`catid`, tg.`description`
			 FROM
				(SELECT
					  o.`tpid`, o.`trid`
					, o.`refid`
					, o.`detail1` `problem`
					, o.`text1` `detailproblem`
					, o.`detail2` `objective`
					, o.`text2` `detailobjective`
					, o.`text3` `indicator`
					, o.`num1` `problemsize`
					, o.`num2` `targetsize`
					, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
				FROM %project_tr% o
				WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "problem"
				) a
				INNER JOIN %tag% tg ON tg.`taggroup` = :taggroup AND tg.`catid` = a.`refid`
			-- WHERE tg.`taggroup`=:taggroup
			-- ORDER BY `catid` ASC;
			';

		$stmt='SELECT * FROM
				(SELECT
				  o.`tpid`, o.`trid`
				, o.`refid`
				, o.`detail1` `problem`
				, o.`text1` `detailproblem`
				, o.`detail2` `objective`
				, o.`text2` `detailobjective`
				, o.`text3` `indicator`
				, o.`num1` `problemsize`
				, o.`num2` `targetsize`
				, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
				, tg.`catid`, tg.`description`
				, tg.`weight`
				, IF(tg.`process` > 0 OR tg.`process` IS NULL, 1, 0) `process`
				FROM %project_tr% o
					LEFT JOIN %tag% tg ON tg.`taggroup` = :taggroup AND tg.`catid` = o.`refid`
				WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "problem"
				) a
			UNION ALL
			SELECT * FROM
				(SELECT
				  NULL `tpid`, NULL `trid`
				, `catid` `refid`
				, `name` `problem`
				, NULL `detailproblem`
				, NULL `objective`
				, NULL `detailobjective`
				, NULL `indicator`
				, NULL `problemsize`
				, NULL `targetsize`
				, NULL `uid`, NULL `created`, NULL `modified`, NULL `modifyby`
				, `catid`, `description`
				, tg.`weight`
				, tg.`process`
				FROM %tag% tg
					LEFT JOIN %project_tr% tr
						ON tr.`tpid` = :tpid AND tr.`formid` = :tagname
						AND tr.`part` = "problem" AND tr.`refid` = tg.`catid`
				WHERE `taggroup` = :taggroup AND tr.`trid` IS NULL
				) b
			ORDER BY IF(`refid` IS NULL,`trid`,`weight`) ASC, `refid` ASC
				';

		$dbs=mydb::select($stmt,':tpid', $planningId, ':tagname',$tagname, ':taggroup','project:problem:'.$result->info->planGroup);
		$result->problem=$dbs->items;
		$result->planInfoQuery='<pre>'.mydb()->_query.'</pre>';
		foreach ($result->problem as $key => $value) {
			if (empty($value->indicator) && $value->description) {
				$detail=json_decode($value->description);
				//debugMsg($detail,'$detail');
				$detail->indicator=str_replace('<br />',"\n",$detail->indicator);
				$result->problem[$key]->indicator=$detail->indicator;
			}
		}

		// Get Objective
		$stmt='SELECT
				o.`tpid`, o.`trid`
				, o.`detail1` `title`
				, o.`text1` `indicator`
				, o.`num1` `problemsize`
				, o.`num2` `targetsize`
				, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
			FROM %project_tr% o
			WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "objective"
			ORDER BY o.`trid` ASC';
		$dbs=mydb::select($stmt,':tpid', $planningId, ':tagname',$tagname);
		$result->objective=$dbs->items;




		// Get Guideline
		$stmt = 'SELECT
				o.`tpid`, o.`trid`
				, o.`text1` `title`
				, o.`text2` `action`
				, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
			FROM %project_tr% o
			WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "guideline"
			ORDER BY o.`trid` ASC';

		$stmt = 'SELECT * FROM
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
				LEFT JOIN %tag% tg ON tg.`taggroup` = :taggroup AND tg.`catid` = o.`refid`
			WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "guideline") a
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
				WHERE `taggroup` = :taggroup AND tr.`trid` IS NULL) b
			ORDER BY IF(`refid` IS NULL,`trid`,`refid`) ASC
			';

		$dbs=mydb::select($stmt,':tpid', $planningId, ':tagname',$tagname, ':taggroup','project:guideline:'.$result->info->planGroup);
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

		// Get Project
		$stmt = 'SELECT
				o.`tpid`, o.`trid`, d.`tpid` `refid`
				, o.`detail1` `title`
				, o.`detail2` `owner`
				, o.`num1` `budget`
				, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
			FROM %project_tr% o
				LEFT JOIN %project_dev% d ON d.`tpid` = o.`refid`
			WHERE o.`tpid` = :tpid AND o.`formid` = :tagname AND o.`part` = "project"
			ORDER BY o.`trid` ASC';

		$dbs = mydb::select($stmt, ':tpid', $planningId, ':tagname', $tagname);
		$result->project = $dbs->items;
		//debugMsg(mydb()->_query);

		return $result;
	}

	public static function items($conditions = [], $options = '{}') {
		$conditions = (Object) $conditions;

		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if ($debug) debugMsg($conditions, '$conditions');
		if ($debug) debugMsg($options, '$options');

		$result = (Object)[];

		// $issueId = $this->issueId;
		// $planTypeSelect = SG\getFirst(post('type'),NULL);
		// $planSelect = SG\getFirst(post('pn'),NULL);
		// $provinceSelect = SG\getFirst(post('pv'),NULL);
		// $ampurSelect = SG\getFirst(post('am'), NULL);
		// $areaSelect = SG\getFirst(post('ar'),NULL);
		// $sectorSelect = SG\getFirst(post('s'),NULL);
		// $yearSelect = SG\getFirst(post('yr'),NULL);

		$orderKey = SG\getFirst($options->order,'date');
		$orderList = [
			'tpid'=>'p.`tpid`',
			'title' => 'CONVERT(`title` USING tis620)',
			'prov' => 'CONVERT(`provname` USING tis620)',
			'date' => '`created` DESC',
			'mod' => '`lastModified` DESC',
			'tran' => '`totalTran` DESC',
			'rate' => '`rating` DESC',
		];
		$order = $orderList[$orderKey];
		if (empty($order)) $order = $orderList['date'];


		$fields = [];
		$joins = [];

		mydb::where('p.`prtype` = "แผนงาน"');
		if ($conditions->userId) mydb::where('t.`uid` = :userId', ':userId', $conditions->userId);
		if ($conditions->childOf) mydb::where('t.`parent` IN ( :parent )', ':parent', 'SET:'.$conditions->childOf);
		if ($conditions->plan) mydb::where('pt.`refid` = :planId', ':planId', $conditions->plan);
		if ($conditions->problem) {
			mydb::where('pb.`refid` = :problemId', ':problemId', $conditions->problem);
			$fields[] = 'pb.`refid` `problemId`, pb.`num1` `problemSize`, pb.`num2` `targetSize`';
			$joins[] = 'LEFT JOIN %project_tr% pb ON pb.`tpid` = p.`tpid` AND pb.`formid` = "info" AND pb.`part` = "problem"';
		}

		if ($conditions->year) mydb::where('p.`pryear` IN ( :year )', ':year', 'SET:'.$conditions->year);


		// TODO: Bug สำหรับเว็บอื่น หากไม่ส่งค่า planType จะไม่สามารถดึงแผนงานได้ เนื่องจาก orgid = NULL
		if ($conditions->planType === 'ampur') {
			mydb::where('t.`orgid` IS NULL AND LENGTH(t.`areacode` = 4)');
		} else {
			mydb::where('t.`orgid` IS NOT NULL');
		}
		if ($conditions->orgId) mydb::where('t.`orgid` = :orgId', ':orgId', $conditions->orgId);
		if ($conditions->childOfOrg) mydb::where('o.`parent` IN ( :orgParent )', ':orgParent', 'SET:'.$conditions->childOfOrg);
		if ($conditions->sector) mydb::where('o.`sector` IN ( :sector )', ':sector', 'SET:'.$conditions->sector);


		if ($conditions->area) {
			mydb::where('f.`areaid` IN ( :areaid )', ':areaid', 'SET:'.$conditions->area);
			$joins[] = 'LEFT JOIN %project_fund% f ON f.`orgid` = t.`orgid`';
		}

		if ($conditions->ampur) {
			mydb::where('CONCAT(o.`changwat`,o.`ampur`) IN ( :filterAmpur )', ':filterAmpur', 'SET-STRING:'.$conditions->ampur);
		} else if ($conditions->changwat) {
			mydb::where('o.`changwat` IN ( :changwat )', ':changwat', 'SET:'.$conditions->changwat);
		}

		mydb::value('$FIELDS$', ($fields ? ', ' : '').implode(_NL.', ', $fields), false);
		mydb::value('$JOINS$', implode(_NL, $joins), false);
		mydb::value('$ORDER$', $order, false);

		$stmt = 'SELECT
			  p.`tpid` `planningId`
			, t.`orgid` `orgId`
			, t.`title`
			, t.`rating`
			, o.`name` `orgName`
			, u.`username`, u.`name` `ownerName`
			, LEFT(t.`areacode`,2) `changwat`
			, cop.`provname` `changwatName`
			, LEFT(t.`areacode`,4) `ampur`
			, cod.`distname` `ampurName`
			$FIELDS$
			, t.`created`
			, (SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = t.`tpid` AND (`formid` = "info" AND `part` IN ("problem", "basic", "guideline", "project")) ) `totalTran`
			, (SELECT GREATEST(IFNULL(MAX(lm.`modified`),0),MAX(lm.`created`)) FROM %project_tr% lm WHERE lm.`tpid` = p.`tpid` AND lm.`formid` = "info") `lastModified`
			FROM %project% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				LEFT JOIN %users% u ON u.`uid` = t.`uid`
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`,2)
				LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(t.`areacode`,4)
				LEFT JOIN %project_tr% pt ON pt.`tpid` = p.`tpid` AND pt.`formid` = "info" AND pt.`part` = "title"
				$JOINS$
			%WHERE%
			GROUP BY p.`tpid`
			ORDER BY $ORDER$
			';

		$result = mydb::select($stmt)->items;

		if ($options->debug) debugMsg('<pre>'.mydb()->_query.'</pre>');

		return $result;
	}
}
?>