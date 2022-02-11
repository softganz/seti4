<?php
/**
* Create Project Development
*
* @param Object $data
* @return Object $options
*/

$debug = true;

function r_project_develop_create($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'tpid' => NULL,
		'data' => (Object) [],
		'dataFirebase' => [],
		'onProjectCreate' => NULL,
		'querys' => [],
	];

	$tpid=false;
	// Create member first project
	if ($data->title) {
		$data->projectset = $data->projectset == 'top' ? NULL : $data->projectset;

		$topic = new stdClass();
		$topic->tpid = NULL;
		$topic->revid = NULL;
		$topic->type = 'project-develop';
		$topic->parent = $data->projectset;
		$topic->status = _DRAFT;
		$topic->orgid = NULL;
		if ($topic->parent) {
			$parentInfo = mydb::select('SELECT * FROM %topic% WHERE `tpid` = :parent LIMIT 1', ':parent', $topic->parent);
			$topic->orgid = $parentInfo->orgid;
		} else if ($data->orgid) {
			$topic->orgid = $data->orgid;
		}
		$topic->uid = i()->uid;
		$topic->title = $data->title;
		$topic->changwat = $data->changwat;
		$topic->created = SG\getFirst($data->created, date('Y-m-d H:i:s'));
		$topic->timestamp = date('Y-m-d H:i:s');
		$topic->ip = ip2long(GetEnv('REMOTE_ADDR'));
		$stmt='INSERT INTO %topic%
			(
			  `type`,`status`,`orgid`,`uid`, `parent`
			, `title`
			, `changwat`,`created`,`ip`
			)
			VALUES
			(
			  :type,:status,:orgid,:uid,:parent
			, :title
			, :changwat,:created,:ip
			);
			-- {debug: true}';
		mydb()->_debug = true;
		mydb::query($stmt,$topic);
		mydb()->_debug = false;
		$result->querys[] = mydb()->_query;

		if (!mydb()->_error) {
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
			$project = new stdClass();
			$project->tpid = $tpid;
			$project->prtype = $data->prtype ? $data->prtype : 'โครงการ';
			$project->projectset = $data->projectset;
			$project->date_approve = empty($data->date_approve) ? NULL : $data->date_approve;
			if (!empty($data->pryear)) $project->pryear = $data->pryear;
			else if ($project->date_approve) $project->pryear = sg_date($project->date_approve,'Y');
			else $project->pryear = date('Y');
			$project->budget = empty($data->budget) ? 0 : sg_strip_money($data->budget);
			$project->changwat = empty($data->changwat) ? NULL : $data->changwat;
			$project->ampur = empty($data->ampur) ? NULL : $data->ampur;
			$project->tambon = empty($data->tambon) ? NULL : $data->tambon;
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

			// Trick firebase update
			$firebase=new Firebase('sg-project-man','update');
			$dataFirebase = [
				'tpid'=>$tpid,
				'tags'=>'Project Create',
				'value'=>$topic->title,
				'orgid'=>SG\getFirst($topic->orgid,''),
				'url'=>_DOMAIN.url('project/develop/'.$tpid),
				'time'=>array('.sv'=>'timestamp')
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

	}
	return $result;
}
?>