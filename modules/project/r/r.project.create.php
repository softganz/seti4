<?php
/**
* Project Model :: Create new project
* Created 2021-01-01
* Modify  2021-02-02
*
* @param Object $data
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("project.create", $user, $options)
*/

$debug = true;

import('model:project.follow.php');

// @deprecated
function r_project_create($data, $options = '{}') {
	return ProjectFollowModel::create($data, $options);


	// Not Used Code, Please Delete
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$tpid = false;
	$result = NULL;
	$result->projectId = NULL;
	$result->tpid = NULL;
	$result->data = $data;
	$result->_query = NULL;

	// Create member first project
	if ($data->title) {
		$data->projectset = $data->projectset == 'top' ? NULL : $data->projectset;

		if ($data->projectset) $projectSetInfo = R::Model('project.get', $data->projectset);

		$topic = new stdClass();
		$topic->tpid = NULL;
		$topic->revid = NULL;
		$topic->type = 'project';
		$topic->parent = $data->projectset;
		$topic->status = _LOCK;
		$topic->orgid = empty($data->orgid)?NULL:$data->orgid;
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
			, `changwat`
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
			, :changwat
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
			$project->projectset = $data->projectset;
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
?>