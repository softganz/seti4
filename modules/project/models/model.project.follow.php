<?php
/**
* Project Model :: Follow Data Model
* Created 2021-09-27
* Modify 	2021-09-27
*
* @param Array $args
* @return Object
*
* @usage new ProjectFollowModel([])
* @usage ProjectFollowModel::function($conditions, $options)
*/

import('model:project.php');

class ProjectFollowModel extends ProjectModel {

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
			$conditions = (Object) ['id' => $conditions];
		}

		if ($debug) {debugMsg($conditions, '$conditions'); debugMsg($options, '$options');}

		if ($conditions->projectType == 'all') {
		} else if ($conditions->projectType) {
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

		if ($conditions->orgId) mydb::where('(t.`orgid` = :orgId'.($options->includeChildOrg ? ' || o.`parent` = :orgId' : '').')', ':orgId', $conditions->orgId);

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

					// Replace space and comma with OR condition
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
			, parent.`title` `parentTitle`
			, t.`areacode`
			, CONCAT(X(p.`location`), ",", Y(p.`location`)) `location`
			, o.`name` `orgName`
			, u.`username`, u.`name` `ownerName`
			, (SELECT COUNT(*) FROM %topic% t WHERE t.`parent` = p.`tpid`) `childCount`
			, DATE_FORMAT(t.`created`, "%Y-%m-%d %H:%i:%s") `created`
			FROM %project% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				LEFT JOIN %users% u ON u.`uid` = t.`uid`
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
				LEFT JOIN %topic% parent ON parent.`tpid` = t.`parent`
				LEFT JOIN %topic_user% tu ON tu.`tpid` = p.`tpid`
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

		$result = (Object) [
			'count' => 0,
			'items' => mydb::select($stmt)->items,
		];
		$result->count = count($result->items);

		if ($debug) debugMsg(mydb()->_query);

		return $result;
	}

	public static function create($data, $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$tpid = false;
		$result = (Object) [
			'projectId' => NULL,
			'tpid' => NULL,
			'data' => $data,
			'_query' => NULL,
		];

		// Create member first project
		if ($data->title) {
			$data->parent = $data->parent === 'top' ? NULL : $data->parent;

			if ($data->parent) $projectSetInfo = R::Model('project.get', $data->parent);

			$topic = new stdClass();
			$topic->tpid = NULL;
			$topic->revid = NULL;
			$topic->type = 'project';
			$topic->parent = $data->parent;
			$topic->status = _LOCK;
			$topic->orgid = empty($data->orgid) ? NULL : $data->orgid;
			$topic->uid = SG\getFirst($data->uid,i()->uid);
			$topic->title = $data->title;
			$topic->areacode = SG\getFirst($data->areacode);
			$topic->changwat = $data->changwat;
			$topic->created = $topic->timestamp=date('Y-m-d H:i:s');
			$topic->ip = ip2long(GetEnv('REMOTE_ADDR'));

			$stmt = 'INSERT INTO %topic%
				(
				  `type`
				, `status`
				, `orgid`
				, `uid`
				, `parent`
				, `title`
				, `areacode`
				, `created`
				, `ip`
				)
				VALUES
				(
				  :type
				, :status
				, :orgid
				, :uid
				, :parent
				, :title
				, :areacode
				, :created
				, :ip
				)';

			mydb::query($stmt,$topic);

			$result->_query[] = mydb()->_query;

			if (!mydb()->_error) {
				$result->projectId = $result->tpid = $tpid = $topic->tpid = mydb()->insert_id;

				// Create topic_revisions
				$stmt = 'INSERT INTO %topic_revisions% (`tpid`,`uid`,`title`,`timestamp`) VALUES (:tpid,:uid,:title,:timestamp)';
				mydb::query($stmt,$topic);
				$result->_query[] = mydb()->_query;

				// Update revid to topic
				$revid = $topic->revid = mydb()->insert_id;
				mydb::query('UPDATE %topic% SET `revid` = :revid WHERE `tpid` = :tpid LIMIT 1',$topic);
				$result->_query[] = mydb()->_query;

				// Create topic_user
				$memberShipType = SG\getFirst($projectSetInfo->info->membership[i()->uid],'OWNER');
				mydb::query('INSERT INTO %topic_user% (`tpid`, `uid`, `membership`) VALUES (:tpid, :uid, :membership)', $topic, ':membership', $memberShipType);
				$result->_query[] = mydb()->_query;

				// Create project
				$project = new stdClass();
				$project->tpid = $tpid;
				$project->prtype = $data->prtype ? $data->prtype : 'โครงการ';
				$project->ischild = empty($data->ischild) ? 0 : $data->ischild;
				$project->projectset = $data->parent;
				$project->date_approve = empty($data->date_approve)?NULL:$data->date_approve;
				if (!empty($data->pryear)) $project->pryear = $data->pryear;
				else if ($project->date_approve) $project->pryear = sg_date($project->date_approve,'Y');
				else $project->pryear = date('Y');
				$project->date_from = SG\getFirst($data->date_from);
				$project->date_end = SG\getFirst($data->date_end);
				$project->budget = empty($data->budget)?0:sg_strip_money($data->budget);
				$project->changwat = empty($data->changwat) ? NULL : $data->changwat;
				$project->ampur = empty($data->ampur) ? NULL : $data->ampur;
				$project->tambon = empty($data->tambon) ? NULL : $data->tambon;
				$project->location = empty($data->location) ? NULL : 'func.PointFromText("POINT('.preg_replace('/,/',' ',$data->location).')")';
				$project->tagname = _PROJECT_TAGNAME;

				$stmt = 'INSERT INTO %project%
					(
					  `tpid`
					, `prtype`
					, `pryear`
					, `projectset`
					, `ischild`
					, `budget`
					, `changwat`
					, `ampur`
					, `tambon`
					, `date_approve`
					, `date_from`
					, `date_end`
					, `location`
					)
					VALUES
					(
					  :tpid
					, :prtype
					, :pryear
					, :projectset
					, :ischild
					, :budget
					, :changwat
					, :ampur
					, :tambon
					, :date_approve
					, :date_from
					, :date_end
					, :location
					)';

				mydb::query($stmt,$project);

				$result->_query[] = mydb()->_query;

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
				$dataFirebase = array('tpid'=>$tpid,'tags'=>'Project Create','value'=>$topic->title,'orgid'=>SG\getFirst($topic->orgid,''),'url'=>_DOMAIN.url('project/'.$tpid),'time'=>array('.sv'=>'timestamp'));
				$firebase->post($dataFirebase);

				$result->dataFirebase = $dataFirebase;

				R::model('watchdog.log','project','Create',$topic->title, i()->uid, $topic->tpid);
				if (post('abtest')) {
					R::model('watchdog.log','abtest','Project Create',$topic->title, i()->uid, $topic->tpid, post('abtest'));
				}
				//function r_watchdog_log($module = NULL, $keyword = NULL, $message = NULL, $uid = NULL, $keyid = NULL, $fldname = NULL) {

			}

			if ($debug) {
				debugMsg($data,'$data');
				debugMsg($topic,'$topic');
				debugMsg($project,'$project');
				debugMsg($result->_query,'$result->_query');
			}
		}


		$result->data = $data;

		$result->onProjectCreate = R::On('project.create', $result);

		return $result;
	}

	public static function getPeriod($projectId, $period = NULL) {
		mydb::where('`tpid` = :projectId AND `formid` = "info" AND `part` = "period"', ':projectId', $projectId);
		if ($period) mydb::where('`period` = :period', ':period', $period);

		mydb::value('$LIMIT$', $period ? 'LIMIT 1' : '');

		$dbs = mydb::select(
			'SELECT
				t.`tpid` `projectId`
				, t.`trid` `periodTranId`
				, t.`period`
				, t.`flag` `financialStatus`
				, t.`uid`
				, t.`num1` `budget`
				, t.`date1` `fromDate`, t.`date2` `toDate`
				, t.`detail1` `reportFromDate`
				, t.`detail2` `reportToDate`
				, t.`text1` `noteOwner`
				, t.`text2` `noteComplete`
				, t.`text3` `noteTrainer`
				, t.`text4` `noteManager`
				, t.`text5` `noteGranter`
				, t.`created`, t.`modified`, t.`modifyby`
			FROM %project_tr% t
			%WHERE%
			ORDER BY `period` ASC
			$LIMIT$;
			-- {key: "period"}'
		);

		if ($period) {
			$result = mydb::clearprop($dbs);
			if ($result->periodTranId) {
				$summary = mydb::clearprop(
					mydb::select(
						'SELECT
							`trid` `summaryTranId`
							, `num1` `openBalance`
							, `num2` `incomeGrant`
							, `num3` `incomeInterest`
							, `num4` `incomeOther`
							, `num1` + `num2` + `num3` + `num4` `incomeTotal`
							, `num5` `pettyCash`
							, `num6` `bankBalance`
							, `flag` `withdrawNextPeriod`
							, `num10` `withdrawNextMoney`
							, `detail1` `signDate`
							, `detail4` `signOfficerName`
							, `detail2` `signOfficerDate`
						FROM %project_tr%
						WHERE `tpid` = :projectId AND `formId` = :formId AND `part` = "summary" AND `period` = :period
						LIMIT 1',
						[
							':projectId' => $projectId,
							':formId' => 'ง.1',
							':period' => $period,
						]
					)
				);
				foreach ($summary as $key => $value) $result->{$key} = $value;
			}
		} else {
			$result = $dbs->items;
		}
		return $result;
	}

	public static function getProgressReport($projectId, $period = NULL) {
		mydb::where('t.`tpid` = :projectId AND t.`formid` = "info" AND t.`part` = "period"', ':projectId', $projectId);
		if ($period) mydb::where('t.`period` = :period', ':period', $period);

		mydb::value('$LIMIT$', $period ? 'LIMIT 1' : '');

		$dbs = mydb::select(
			'SELECT
				t.`tpid` `projectId`
				, t.`trid` `periodTranId`
				, pg.`trid` `progressTranId`
				, t.`period`
				, t.`flag` `financialStatus`
				, pg.`flag` `progressStatus`
				, t.`uid`
				, t.`num1` `budget`
				, t.`date1` `fromDate`, t.`date2` `toDate`
				, t.`detail1` `reportFromDate`
				, t.`detail2` `reportToDate`
				, pg.`detail1` `reportDate`
				, t.`created`, t.`modified`, t.`modifyby`
			FROM %project_tr% t
				LEFT JOIN %project_tr% pg ON pg.`tpid` = t.`tpid` AND pg.`formId` = "ส.1" AND pg.`part` = "title" AND pg.`period` = t.`period`
			%WHERE%
			ORDER BY t.`period` ASC
			$LIMIT$;
			-- {key: "period"}'
		);

		if ($period) {
			$result = mydb::clearprop($dbs);
		} else {
			$result = $dbs->items;
		}
		return $result;
	}
}
?>