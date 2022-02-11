<?php
/**
* Project Model : Post/Update Project Action
* Created 2021-01-01
* Modify  2021-02-14
*
* @param Object $projectInfo
* @param Object $post
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("project.action.save", $projectInfo, $post, $options)
*/

function r_project_action_save($projectInfo, $post, $options = '{}') {
	$defaults = '{debug:false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'actionId' => $data->actionId,
		'_error' => false,
		'data' => (Object) [],
		'querys' => [],
	];
	$projectId = $projectInfo->tpid;
	$part = SG\getFirst($post->part, 'owner');

	if ($debug) debugMsg($options, '$options');

	if ($debug) debugMsg($post, '$post');


	$isAdmin = user_access('administer projects');
	$isEdit = user_access('administer projects') || (project_model::is_owner_of($projectId) && $part == "owner") || (project_model::is_trainer_of($projectId));
	//if (!$isEdit) return false;

	$lockReportDate = project_model::get_lock_report_date($projectId);



	// Create new activity and use this refid
	if ($projectId && $post->newcalendar) {
		$calendarData = (Object) [
			'tpid' => $projectId,
			'owner' => i()->uid,
			'privacy' => 'public',
			'title' => $post->title,
			'from_date' => $post->actionDate ? sg_date($post->actionDate, 'Y-m-d') : NULL,
			'from_time' => NULL,
			'detail' => $post->detail,
		];

		$result->calendar = R::Model('project.calendar.save', $projectInfo, $calendarData);

		$post->activityId = $result->calendar->refid;
		$post->calid = $result->calendar->calid;

		//debugMsg($result,'$result');
		//debugMsg($calendarData,'$calendarData');
	} else if ($projectId && empty($post->activityId)) {
		// TODO : Add project_tr formid=info part=activity
	}






	if (empty($projectId)) {
		$result->_error = 'ไม่มีข้อมูลโครงการ';
	} else if (empty($post->calid)) {
		$result->_error = 'ไม่มีข้อมูลปฏิทินกิจกรรม';
	} else if (empty($post->activityId)) {
		//$result->_error = 'ไม่มีข้อมูลแผนการดำเนินงาน';
	}
	if ($result->_error) return $result;


	$error = false;

	$data = (Object) [
		'actionId' => empty($post->actionId) ? NULL : $post->actionId,
		'tpid' => $projectId,
		'calid' => $post->calid,
		'activityId' => intval($post->activityId),
		'formid' => 'activity',
		'part' => $post->part,
		'title' => $post->title,
		'actionDate' => $post->actionDate ? sg_date($post->actionDate, 'Y-m-d') : NULL,
		'actionTime' => $post->actionTime,
		'uid' => i()->uid,
		'flag' => _PROJECT_COMPLETEPORT,
		'created' => date('U'),
		'modified' => date('U'),
		'modifyby' => i()->uid,
	];

	$isNewAction = empty($data->actionId);

	$stmt = 'UPDATE %calendar% SET `title` = :title WHERE `id` = :calid LIMIT 1';

	mydb::query($stmt, $data);

	$result->querys[] = mydb()->_query;

	$data->exp_meed = sg_strip_money($post->exp_meed);
	$data->exp_wage = sg_strip_money($post->exp_wage);
	$data->exp_supply = sg_strip_money($post->exp_supply);
	$data->exp_material = sg_strip_money($post->exp_material);
	$data->exp_utilities = sg_strip_money($post->exp_utilities);
	$data->exp_other = sg_strip_money($post->exp_other);
	$data->exp_travel = sg_strip_money($post->exp_travel);
	$data->exp_total = sg_strip_money($post->exp_total);

	$data->actionReal = $post->actionReal;
	$data->outputOutcomeReal = $post->outputOutcomeReal;


	// If no input form, don't save field
	$data->rate1 = NULL;
	if (empty($post->rate1)) $data->rate1 = 0;
	else if ($post->rate1 == -1) $data->rate1 = NULL;
	else $data->rate1 = $post->rate1;

	$data->rate2 = NULL;
	if (empty($post->rate2)) $data->rate2 = 0;
	else if ($post->rate2 == -1) $data->rate2 = NULL;
	else $data->rate2 = $post->rate2;

	$data->targetJoinAmt = abs(intval(sg_strip_money($post->targetJoinAmt)));
	$data->targetJoinDetail = $post->targetJoinDetail;

	$data->objectiveDetail = $post->objectiveDetail;
	$data->problem = $post->problem;
	$data->recommendation = $post->recommendation;
	$data->support = $post->support;
	$data->followerRecommendation = $post->followerRecommendation;
	$data->followerName = $post->followerName;
	$data->jobType = SG\getFirst($post->jobType);

	if (R()->appAgent) {
		$data->appsrc = R()->appAgent->OS;
		$data->appagent = R()->appAgent->dev.'/'.R()->appAgent->ver.' ('.R()->appAgent->type.';'.R()->appAgent->OS.')';
	} else if (preg_match('/app/',$_SERVER["HTTP_REFERER"])) {
		$data->appsrc = 'Web App';
		$data->appagent = 'Web App';
	} else {
		$data->appsrc = 'Web';
		$data->appagent = 'Web';
	}

	$fields = [];
	if (property_exists($post,'rate1')) $fields['rate1'] = 'rate1';
	if (property_exists($post,'rate2')) $fields['rate2'] = 'rate2';
	if (property_exists($post,'targetJoinAmt')) $fields['num8'] = 'targetJoinAmt';
	if (property_exists($post,'targetJoinDetail')) $fields['text9'] = 'targetJoinDetail';

	if (property_exists($post,'objectiveDetail')) $fields['detail3'] = 'objectiveDetail';
	if (property_exists($post,'problem')) $fields['text5'] = 'problem';
	if (property_exists($post,'recommendation')) $fields['text6'] = 'recommendation';
	if (property_exists($post,'support')) $fields['text7'] = 'support';
	if (property_exists($post,'followerRecommendation')) $fields['text8'] = 'followerRecommendation';
	if (property_exists($post,'followerName')) $fields['detail2'] = 'followerName';
	if (property_exists($post,'jobType')) $fields['detail4'] = 'jobType';

	$insertField = $insertValue = $updateField = '';
	foreach ($fields as $key => $value) {
		$insertField .= ', `'.$key.'`'._NL;
		$insertValue .= ', :'.$value._NL;
		$updateField .= ', `'.$key.'` = :'.$value._NL;
	}
	mydb::value('$INSERTFIELD$', $insertField, false);
	mydb::value('$INSERTVALUE$', $insertValue, false);
	mydb::value('$UPDATEFIELD$', $updateField, false);

	// Start save data
	// Create new item on calendar when no calid, no $_REQUEST[calid] and not select calendar item from list
	/*
		if (empty($data->calid) && $data->activityname) {
			$calendar->tpid=$data->tpid;
			$calendar->owner=$data->uid;
			$calendar->privacy='public';
			$calendar->title=$data->activityname;
			$calendar->from_date=$data->date1;
			$calendar->from_time=$data->detail1;
			$calendar->detail=$data->text2;
			$calendar->ip=ip2long(GetEnv('REMOTE_ADDR'));
			$calendar->created_date=date('Y-m-d H:i:s');
			$stmt='INSERT INTO %calendar% (`tpid`, `owner`, `privacy`, `title`, `from_date`, `from_time`, `detail`, `ip`, `created_date`) VALUES (:tpid, :owner, :privacy, :title, :from_date, :from_time, :detail, :ip, :created_date)';
			mydb::query($stmt,$calendar);

			$data->calid=mydb()->insert_id;
		}
		unset($data->detail);
		*/




		/*
		$stmt='INSERT INTO %project_tr_bak%
						(`trid`, `tpid`, `parent`, `calid`, `formid`, `part`, `flag`, `uid`, `date1`, `detail1`, `detail2`,`detail3`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`, `text7`, `text8`, `text9`, `rate1`, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7`, `num8`, `created`)
					VALUES
						(:trid, :tpid, :parent, :calid, :formid, :part, :flag, :uid, :date1, :detail1, :detail2, :detail3, :text1, :text2, :text3, :text4, :text5, :text6, :text7, :text8, :text9, :rate1, :num1, :num2, :num3, :num4, :num5, :num6, :num7, :num8, :created)
					ON DUPLICATE KEY
					UPDATE `calid`=:calid, `part`=:part, `flag`=:flag, `date1`=:date1, `detail1`=:detail1, `detail2`=:detail2, `detail3`=:detail3, `text1`=:text1, `text2`=:text2, `text3`=:text3, `text4`=:text4, `text5`=:text5, `text6`=:text6, `text7`=:text7, `text8`=:text8, `text9`=:text9, `rate1`=:rate1, `num1`=:num1, `num2`=:num2, `num3`=:num3, `num4`=:num4, `num5`=:num5, `num6`=:num6, `num7`=:num7, `num8`=:num8, `modified`=:modified, modifyby=:modifyby ;';
		mydb::query($stmt,$data);
	*/


	$result->data = $data;


	$stmt = 'INSERT INTO %project_tr% (
			`trid`, `tpid`, `calid`, `refid`
			, `formid`
			, `part`
			, `flag`
			, `uid`
			, `date1`
			, `detail1`
			, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num9`
			, `num7`
			, `text2`
			, `text4`
			$INSERTFIELD$
			, `appagent`
			, `appsrc`
			, `created`
		) VALUES (
			:actionId, :tpid, :calid, :activityId
			, :formid
			, :part
			, :flag
			, :uid
			, :actionDate
			, :actionTime
			, :exp_meed, :exp_wage, :exp_supply
			, :exp_material, :exp_utilities, :exp_other, :exp_travel
			, :exp_total
			, :actionReal
			, :outputOutcomeReal
			$INSERTVALUE$
			, :appagent
			, :appsrc
			, :created
		) ON DUPLICATE KEY UPDATE
			  `date1` = :actionDate
			, `detail1` = :actionTime
			, `num1` = :exp_meed, `num2` = :exp_wage, `num3` = :exp_supply
			, `num4` = :exp_material, `num5` = :exp_utilities, `num6` = :exp_other, `num9` = :exp_travel
			, `num7` = :exp_total
			, `text2` = :actionReal
			, `text4` = :outputOutcomeReal
			$UPDATEFIELD$
			, `modified` = :modified, modifyby = :modifyby
			;';

	mydb::query($stmt, $data);
	$result->querys[] = $stmt;
	$result->querys[] = mydb()->_query;

	$actionId = $result->actionId = is_null($data->actionId) ? mydb()->insert_id : $data->actionId;
	$result->data->actionId = $actionId;


	if ($isNewAction) {
		R::Model('watchdog.log','project','Action create','Project id '.$projectId.' : '.$projectInfo->title.' - Action id '.$actionId.' : '.$data->title.' was created by '.i()->name.'('.i()->uid.')', NULL, $projectId);
	}

	//$data->calowner=$part=='owner'?_PROJECT_OWNER_ACTIVITY:_PROJECT_TRAINER_ACTIVITY;

	/*
	$stmt='INSERT INTO %project_activity% (`calid`, `calowner`, `mainact`, `targetpreset`, `budget`) VALUES (:calid, :calowner, :mainact, :targetpreset, :budget)
					ON  DUPLICATE KEY UPDATE `mainact`=:mainact, `targetpreset`=:targetpreset, `budget`=:budget';
	mydb::query($stmt, ':calid', $data->calid, ':calowner', $data->calowner, ':mainact', $data->mainact, ':targetpreset', $data->targetpreset, ':budget',$data->budget);
	$result->querys[]=mydb()->_query;

	// convert multiple upload file to each upload file
	$photos= array();
	if (is_string($_FILES['photo']['name'])) {
		$photos[]=$_FILES['photo'];
	} elseif (is_array($_FILES['photo']['name'])) {
		foreach ($_FILES['photo']['name'] as $key=>$name) {
			$photos[$key]['name']=$_FILES['photo']['name'][$key];
			$photos[$key]['type']=$_FILES['photo']['type'][$key];
			$photos[$key]['tmp_name']=$_FILES['photo']['tmp_name'][$key];
			$photos[$key]['error']=$_FILES['photo']['error'][$key];
			$photos[$key]['size']=$_FILES['photo']['size'][$key];
		}
	}
	$gallery=SG\getFirst(	mydb::select('SELECT `gallery` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$trid)->gallery,
										mydb::select('SELECT MAX(gallery) lastgallery FROM %topic_files% LIMIT 1')->lastgallery+1);
	$is_upload_photo=false;
	foreach ($photos as $photo) {
		if (!is_uploaded_file($photo['tmp_name'])) continue;
		$upload=new classFile($photo,cfg('paper.upload.photo.folder'),cfg('photo.file_type'));
		if (!$upload->valid_format()) continue;
		if (!$upload->valid_size(cfg('photo.max_file_size')*1024)) {
			sg_photo_resize($upload->upload->tmp_name,cfg('photo.resize.width'),NULL,NULL,true,cfg('photo.resize.quality'));
		}
		if ($upload->duplicate()) $upload->generate_nextfile();
		$photo_upload=$upload->filename;
		$pics_desc['type'] = 'photo';
		$pics_desc['tpid'] = $data->tpid;
		$pics_desc['cid'] = 'func.NULL';
		$pics_desc['gallery'] = $gallery;
		$pics_desc['title']=$data->activityname;
		$pics_desc['uid']=i()->ok?i()->uid:'func.NULL';
		$pics_desc['file']=$photo_upload;
		$pics_desc['timestamp']='func.NOW()';
		$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

		$sql_cmd=mydb::create_insert_cmd('%topic_files%',$pics_desc);
		if ($upload->copy()) {
			mydb::query($sql_cmd,$pics_desc);
			$is_upload_photo=true;
		}
	}
	if ($is_upload_photo) mydb::query('UPDATE %project_tr% SET gallery=:gallery WHERE `trid`=:trid LIMIT 1',':trid',$trid,':gallery',$gallery);
	*/

	// Log calendar create
	$firebaseCfg = cfg('firebase');
	$firebaseFolder = SG\getFirst($firebaseCfg['update'], 'update');
	$firebase = new Firebase('sg-project-man', $firebaseFolder);
	if ($isNewAction) {
		$firebaseData = array(
			'tpid' => $projectId,
			'projectId' => $projectId,
			'tags' => 'Project Action Create',
			'value' => $projectInfo->title.' :: บันทึกกิจกรรม :: '.$data->title,
			'formid' => 'activity',
			'part' => 'owner',
			'actionId' => $actionId,
			'uid' => $data->uid,
			'url' => _DOMAIN.url('project/'.$projectId.'/action.view/'.$actionId),
			'changed' => 'new',
			'time' => array('.sv' => 'timestamp'),
			//'token' => 'BBB',
		);
		$firebase->put($actionId, $firebaseData);
	} else {
		$firebaseData = array(
			'projectId' => $projectId,
			'actionId' => $actionId,
			'changed' => 'update',
			'time' => array('.sv' => 'timestamp'),
		);
		$firebase->set($actionId, $firebaseData);
	}

	//debugMsg('$firebaseFolder = '.$firebaseFolder);
	//debugMsg($firebaseData, '$firebaseData');

	return $result;
}
?>