<?php
function r_project_idea_create($data) {
	$tpid=false;
	if (is_array($data)) $data=(object)$data;
	// Create member first project
	if ($data->title) {
		$topic=new stdClass();
		$topic->tpid=NULL;
		$topic->revid=NULL;
		$topic->type='project.idea';
		$topic->status=_LOCK;
		$topic->orgid=empty($data->orgid)?NULL:$data->orgid;
		$topic->uid=i()->uid;
		$topic->title=$data->title;
		$topic->changwat=$data->changwat;
		$topic->created=$topic->timestamp=date('Y-m-d H:i:s');
		$topic->ip=ip2long(GetEnv('REMOTE_ADDR'));
		$stmt='INSERT INTO %topic% (`type`,`status`,`orgid`,`uid`,`title`,`changwat`,`created`,`ip`) VALUES (:type,:status,:orgid,:uid,:title,:changwat,:created,:ip)';
		mydb::query($stmt,$topic);
		$querys[]=mydb()->_query;

		if (!mydb()->_error) {
			$tpid=$topic->tpid=mydb()->insert_id;

			// Create topic_revisions
			$stmt='INSERT INTO %topic_revisions% (`tpid`,`uid`,`title`,`timestamp`) VALUES (:tpid,:uid,:title,:timestamp)';
			mydb::query($stmt,$topic);
			$querys[]=mydb()->_query;

			// Update revid to topic
			$revid=$topic->revid=mydb()->insert_id;
			mydb::query('UPDATE %topic% SET `revid`=:revid WHERE `tpid`=:tpid LIMIT 1',$topic);
			$querys[]=mydb()->_query;

			// Create topic_user
			mydb::query('INSERT INTO %topic_user% (`tpid`,`uid`,`membership`) VALUES (:tpid,:uid,"Owner")',$topic);
			$querys[]=mydb()->_query;

			// Create project
			$project=new stdClass();
			$project->tpid=$tpid;
			$project->title=$data->title;
			if (!empty($data->ideayear)) $project->ideayear=$data->ideayear;
			else $project->ideayear=date('Y');
			$project->problem=$data->problem;
			$project->activity=$data->activity;
			$project->byname=$data->byname;
			$project->orgname=$data->orgname;
			$project->phone=$data->phone;
			$project->email=$data->email;
			$project->created=date('U');
			$stmt='INSERT INTO %project_idea%
							(`tpid`,`ideayear`,`title`,`problem`,`activity`,`byname`,`orgname`,`phone`,`email`,`created`)
							VALUES
							(:tpid,:ideayear,:title,:problem,:activity,:byname,:orgname,:phone,:email,:created)';
			mydb::query($stmt,$project);
			$querys[]=mydb()->_query;
		}

	}
	//debugMsg(print_o($data,'$data').print_o($topic,'$topic').print_o($project,'$project').print_o($querys,'$querys'));
	return $tpid;
}
?>